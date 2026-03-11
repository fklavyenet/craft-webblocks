<?php

namespace fklavyenet\webblocks\controllers;

use Craft;
use craft\elements\Entry;
use craft\web\Controller;
use yii\web\Response;

/**
 * Handles wbForm submissions.
 *
 * POST  /actions/webblocks/form/submit
 *
 * Expected POST fields:
 *   formEntryId  — ID of the wbForm entry block
 *   CRAFT_CSRF_TOKEN
 *   <field-n>    — one key per wbFormField, keyed by field label slugified
 */
class FormController extends Controller
{
    // Allow anonymous access so front-end visitors can submit
    protected array|bool|int $allowAnonymous = ['submit'];

    /**
     * Processes a wbForm submission.
     *
     * Supports both Ajax (returns JSON) and plain POST (redirects).
     */
    public function actionSubmit(): Response
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();
        $formEntryId = (int) $request->getRequiredBodyParam('formEntryId');

        // Load the form entry
        $formEntry = Entry::find()
            ->id($formEntryId)
            ->status(null)
            ->one();

        if (!$formEntry) {
            return $this->_errorResponse(
                ['form' => Craft::t('site', 'Form not found.')],
                $request->getIsAjax()
            );
        }

        // Collect submitted values and validate required fields
        $errors = [];
        $submittedValues = [];

        foreach ($formEntry->wbFormFields->all() as $fieldEntry) {
            $label = (string) $fieldEntry->wbFormFieldLabel;
            $type = (string) $fieldEntry->wbFormFieldType;
            $required = (bool) $fieldEntry->wbFormFieldRequired;
            $inputName = $this->_fieldKey($label);

            $value = $request->getBodyParam($inputName, '');

            if ($required && trim((string) $value) === '') {
                $errors[$inputName] = Craft::t('site', '{label} is required.', ['label' => $label]);
            }

            $submittedValues[$label] = $value;
        }

        if (!empty($errors)) {
            return $this->_errorResponse($errors, $request->getIsAjax());
        }

        // Send email
        $recipient = (string) $formEntry->wbFormRecipient;
        $subject = (string) $formEntry->wbFormSubject ?: Craft::t('site', 'New form submission');
        $successMsg = (string) $formEntry->wbFormSuccessMsg
            ?: Craft::t('site', 'Thank you! We will be in touch shortly.');

        if ($recipient) {
            $body = $this->_buildEmailBody($submittedValues);

            try {
                Craft::$app->getMailer()
                    ->compose()
                    ->setTo($recipient)
                    ->setSubject($subject)
                    ->setHtmlBody($body)
                    ->setTextBody(strip_tags($body))
                    ->send();
            } catch (\Throwable $e) {
                Craft::error('wbForm mail error: ' . $e->getMessage(), __METHOD__);
                // Don't expose mail errors to the visitor — just log them
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
     * Converts a field label to a safe HTML input name (lowercase, hyphenated).
     */
    private function _fieldKey(string $label): string
    {
        $key = mb_strtolower(trim($label));
        $key = preg_replace('/[^a-z0-9]+/', '-', $key);
        return trim($key, '-');
    }

    /**
     * Builds a simple HTML email body from submitted field values.
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
