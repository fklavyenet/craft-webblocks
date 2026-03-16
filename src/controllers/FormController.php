<?php

namespace fklavyenet\webblocks\controllers;

use Craft;
use craft\elements\Entry;
use craft\web\Controller;
use yii\filters\RateLimiter;
use yii\web\Response;

/**
 * Handles wbForm submissions.
 *
 * POST  /actions/webblocks/form/submit
 *
 * Expected POST fields:
 *   formEntryId  — ID of the wbForm entry block
 *   wbhp         — honeypot field (must be empty)
 *   CRAFT_CSRF_TOKEN
 *   <field-n>    — one key per wbFormField, keyed by field label slugified
 */
class FormController extends Controller
{
    // Allow anonymous access so front-end visitors can submit
    protected array|bool|int $allowAnonymous = ['submit'];

    /**
     * Rate-limit the submit action: max 5 submissions per IP per 60 seconds.
     * Falls back gracefully if IpRateLimitIdentity is not available (Craft < 5.9.15).
     */
    public function behaviors(): array
    {
        $behaviors = parent::behaviors();

        if (class_exists(\craft\filters\IpRateLimitIdentity::class)) {
            $behaviors['rateLimiter'] = [
                'class'                  => RateLimiter::class,
                'only'                   => ['submit'],
                'enableRateLimitHeaders' => false,
                'user' => fn() => new \craft\filters\IpRateLimitIdentity([
                    'limit'     => 5,
                    'window'    => 60,
                    'keyPrefix' => 'wb-form-submit',
                    'ip'        => Craft::$app->getRequest()->getUserIP() ?? 'unknown',
                ]),
            ];
        }

        return $behaviors;
    }

    /**
     * Processes a wbForm submission.
     *
     * Supports both Ajax (returns JSON) and plain POST (redirects).
     */
    public function actionSubmit(): Response
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();

        // ── Honeypot check ────────────────────────────────────────────────────
        $honeypot = $request->getBodyParam('wbhp', '');
        if (trim((string) $honeypot) !== '') {
            // Silently pretend success to fool bots
            if ($request->getIsAjax()) {
                return $this->asJson(['success' => true]);
            }
            Craft::$app->getSession()->setFlash('wbFormSuccess', Craft::t('site', 'Thank you! We will be in touch shortly.'));
            return $this->redirectToPostedUrl();
        }

        $formEntryId = (int) $request->getRequiredBodyParam('formEntryId');

        // Load the form entry — only live (enabled) entries accept submissions
        $formEntry = Entry::find()
            ->id($formEntryId)
            ->one();

        if (!$formEntry) {
            return $this->_errorResponse(
                ['form' => Craft::t('site', 'Form not found.')],
                $request->getIsAjax()
            );
        }

        // Collect submitted values and validate required fields
        $errors        = [];
        $allValues     = [];   // all field values: label => value
        $adminValues   = [];   // filtered for admin email
        $confirmValues = [];   // filtered for confirmation email
        $visitorEmail  = null; // first email-type field value

        foreach ($formEntry->wbFormFields->all() as $fieldEntry) {
            $label        = (string) $fieldEntry->wbFormFieldLabel;
            $type         = (string) $fieldEntry->wbFormFieldType;
            $required     = (bool)   $fieldEntry->wbFormFieldRequired;
            $inAdmin      = (bool)   ($fieldEntry->wbFormFieldInAdminEmail ?? true);
            $inConfirm    = (bool)   ($fieldEntry->wbFormFieldInConfirmEmail ?? false);
            $inputName    = $this->_fieldKey($label);

            $value = $request->getBodyParam($inputName, '');

            if ($required && trim((string) $value) === '') {
                $errors[$inputName] = Craft::t('site', '{label} is required.', ['label' => $label]);
            }

            $allValues[$label] = $value;

            if ($inAdmin) {
                $adminValues[$label] = $value;
            }

            if ($inConfirm) {
                $confirmValues[$label] = $value;
            }

            // Capture the first email-type field as the visitor's address
            if ($type === 'email' && $visitorEmail === null && trim((string) $value) !== '') {
                $candidate = trim((string) $value);
                if (filter_var($candidate, FILTER_VALIDATE_EMAIL)) {
                    $visitorEmail = $candidate;
                }
            }
        }

        if (!empty($errors)) {
            return $this->_errorResponse($errors, $request->getIsAjax());
        }

        $recipient = (string) $formEntry->wbFormRecipient;
        if ($recipient === '') {
            $pluginSettings = \fklavyenet\webblocks\WebBlocks::getInstance()->getSettings();
            $recipient = (string) ($pluginSettings->adminEmail ?? '');
        }
        if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            Craft::error('wbForm: invalid or missing recipient email — skipping admin notification.', __METHOD__);
            $recipient = '';
        }
        $subject    = (string) $formEntry->wbFormSubject ?: Craft::t('site', 'New form submission');
        $successMsg = (string) $formEntry->wbFormSuccessMsg
            ?: Craft::t('site', 'Thank you! We will be in touch shortly.');

        // 1. Save submission entry
        $this->_saveSubmission($formEntry, $allValues, $visitorEmail);

        // 2. Send admin notification email (filtered fields)
        if ($recipient) {
            $body = $this->_buildEmailBody($adminValues);
            try {
                Craft::$app->getMailer()
                    ->compose()
                    ->setTo($recipient)
                    ->setSubject($subject)
                    ->setHtmlBody($body)
                    ->setTextBody(strip_tags($body))
                    ->send();
            } catch (\Throwable $e) {
                Craft::error('wbForm admin mail error: ' . $e->getMessage(), __METHOD__);
            }
        }

        // 3. Send visitor confirmation email (if enabled and email address found)
        $confirmEnabled = (bool) ($formEntry->wbFormConfirmationEnabled ?? false);
        if ($confirmEnabled && $visitorEmail) {
            $confirmSubject = (string) ($formEntry->wbFormConfirmationSubject ?? '')
                ?: Craft::t('site', 'We received your message');
            $confirmBody = (string) ($formEntry->wbFormConfirmationBody ?? '');

            // Replace {FieldLabel} placeholders with submitted values
            $confirmBody = $this->_replacePlaceholders($confirmBody, $allValues);

            $confirmHtml = $this->_buildConfirmationBody($confirmBody, $confirmValues);

            try {
                Craft::$app->getMailer()
                    ->compose()
                    ->setTo($visitorEmail)
                    ->setSubject($confirmSubject)
                    ->setHtmlBody($confirmHtml)
                    ->setTextBody(strip_tags($confirmHtml))
                    ->send();
            } catch (\Throwable $e) {
                Craft::error('wbForm confirmation mail error: ' . $e->getMessage(), __METHOD__);
            }
        }

        if ($request->getIsAjax()) {
            return $this->asJson([
                'success' => true,
                'message' => $successMsg,
            ]);
        }

        Craft::$app->getSession()->setFlash('wbFormSuccess', $successMsg);

        return $this->redirectToPostedUrl();
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    /**
     * Saves a wbSubmission entry for every form submission.
     */
    private function _saveSubmission(Entry $formEntry, array $allValues, ?string $visitorEmail): void
    {
        $section = Craft::$app->getEntries()->getSectionByHandle('wbSubmissions');
        if (!$section) {
            Craft::warning('wbSubmissions section not found — skipping submission save.', __METHOD__);
            return;
        }

        $entryType = null;
        foreach ($section->getEntryTypes() as $et) {
            if ($et->handle === 'wbSubmission') {
                $entryType = $et;
                break;
            }
        }

        if (!$entryType) {
            Craft::warning('wbSubmission entry type not found — skipping submission save.', __METHOD__);
            return;
        }

        // Build table rows: one row per submitted field
        $tableRows = [];
        foreach ($allValues as $label => $value) {
            $tableRows[] = [
                'field' => (string) $label,
                'value' => (string) $value,
            ];
        }

        $submission = new Entry();
        $submission->sectionId = $section->id;
        $submission->typeId    = $entryType->id;
        $submission->enabled   = true;

        $submission->setFieldValue('wbSubmissionStatus', 'unread');
        $submission->setFieldValue('wbSubmissionEmail', $visitorEmail ?? '');
        $submission->setFieldValue('wbSubmissionData', $tableRows);

        // Relate to the form entry
        $submission->setFieldValue('wbSubmissionForm', [$formEntry->id]);

        if (!Craft::$app->getElements()->saveElement($submission)) {
            Craft::error(
                'wbForm: failed to save submission — ' . json_encode($submission->getErrors()),
                __METHOD__
            );
        }
    }

    /**
     * Converts a field label to a safe HTML input name (lowercase, hyphenated).
     */
    private function _fieldKey(string $label): string
    {
        $key = mb_strtolower(trim($label));
        $key = preg_replace('/[^a-z0-9]+/', '-', $key);
        return trim($key, '-');
    }

    /**
     * Replaces {FieldLabel} placeholders in a string with submitted values.
     */
    private function _replacePlaceholders(string $text, array $values): string
    {
        foreach ($values as $label => $value) {
            $placeholder = '{' . $label . '}';
            $text = str_replace($placeholder, (string) $value, $text);
        }
        return $text;
    }

    /**
     * Builds an HTML admin notification email body.
     */
    private function _buildEmailBody(array $values): string
    {
        $rows = '';
        foreach ($values as $label => $value) {
            $escapedLabel = htmlspecialchars((string) $label, ENT_QUOTES);
            $escapedValue = nl2br(htmlspecialchars((string) $value, ENT_QUOTES));
            $rows .= "<tr>
                <th style=\"text-align:left;padding:8px 12px;background:#f5f5f5;border:1px solid #ddd;\">{$escapedLabel}</th>
                <td style=\"padding:8px 12px;border:1px solid #ddd;\">{$escapedValue}</td>
            </tr>\n";
        }

        return <<<HTML
<!DOCTYPE html>
<html>
<body style="font-family:sans-serif;font-size:14px;color:#333;">
    <h2>New form submission</h2>
    <table style="border-collapse:collapse;width:100%;max-width:600px;">
        {$rows}
    </table>
</body>
</html>
HTML;
    }

    /**
     * Builds an HTML confirmation email body.
     *
     * The main message body is the CP-authored text (with placeholders already
     * replaced). If any fields were marked "include in confirmation email",
     * they are appended as a summary table below the message.
     */
    private function _buildConfirmationBody(string $bodyText, array $confirmValues): string
    {
        $escapedBody = nl2br(htmlspecialchars($bodyText, ENT_QUOTES));

        $summaryTable = '';
        if (!empty($confirmValues)) {
            $rows = '';
            foreach ($confirmValues as $label => $value) {
                $escapedLabel = htmlspecialchars((string) $label, ENT_QUOTES);
                $escapedValue = nl2br(htmlspecialchars((string) $value, ENT_QUOTES));
                $rows .= "<tr>
                    <th style=\"text-align:left;padding:8px 12px;background:#f5f5f5;border:1px solid #ddd;\">{$escapedLabel}</th>
                    <td style=\"padding:8px 12px;border:1px solid #ddd;\">{$escapedValue}</td>
                </tr>\n";
            }
            $summaryTable = <<<HTML

    <hr style="border:none;border-top:1px solid #ddd;margin:24px 0;">
    <h3 style="font-size:14px;color:#555;">Your submission summary</h3>
    <table style="border-collapse:collapse;width:100%;max-width:600px;">
        {$rows}
    </table>
HTML;
        }

        return <<<HTML
<!DOCTYPE html>
<html>
<body style="font-family:sans-serif;font-size:14px;color:#333;max-width:600px;margin:0 auto;padding:24px;">
    <p style="line-height:1.6;">{$escapedBody}</p>
    {$summaryTable}
</body>
</html>
HTML;
    }

    /**
     * Returns an error response as JSON or sets session errors for non-Ajax.
     */
    private function _errorResponse(array $errors, bool $isAjax): Response
    {
        if ($isAjax) {
            return $this->asJson([
                'success' => false,
                'errors' => $errors,
            ]);
        }

        Craft::$app->getSession()->setError(reset($errors));

        return $this->redirectToPostedUrl();
    }
}
