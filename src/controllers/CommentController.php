<?php

namespace fklavyenet\webblocks\controllers;

use Craft;
use craft\elements\Entry;
use craft\web\Controller;
use yii\web\Response;

/**
 * Handles blog comment submissions.
 *
 * POST  /actions/webblocks/comment/submit
 *
 * Expected POST fields:
 *   postEntryId          — ID of the wbPost entry this comment belongs to
 *   wbCommentAuthorName  — commenter's name
 *   wbEmail              — commenter's email
 *   wbCommentBody        — comment text
 *   wbhp                 — honeypot field (must be empty)
 *   CRAFT_CSRF_TOKEN
 */
class CommentController extends Controller
{
    // Allow anonymous front-end submissions
    protected array|bool|int $allowAnonymous = ['submit'];

    /**
     * Processes a comment submission.
     *
     * New comments are saved as disabled (pending moderation).
     * Supports both Ajax (returns JSON) and plain POST (redirects).
     */
    public function actionSubmit(): Response
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();
        $isAjax  = $request->getIsAjax();

        // ── Honeypot check ────────────────────────────────────────────────────
        $honeypot = $request->getBodyParam('wbhp', '');
        if (trim((string) $honeypot) !== '') {
            // Silently pretend success to fool bots
            return $this->_successResponse($isAjax);
        }

        // ── Validate post entry ───────────────────────────────────────────────
        $postEntryId = (int) $request->getBodyParam('postEntryId', 0);
        $postEntry   = $postEntryId
            ? Entry::find()->id($postEntryId)->section('wbBlog')->status(null)->one()
            : null;

        if (!$postEntry) {
            return $this->_errorResponse(
                ['form' => Craft::t('site', 'Post not found.')],
                $isAjax
            );
        }

        // ── Collect & validate fields ─────────────────────────────────────────
        $authorName  = trim((string) $request->getBodyParam('wbCommentAuthorName', ''));
        $email       = trim((string) $request->getBodyParam('wbEmail', ''));
        $body        = trim((string) $request->getBodyParam('wbCommentBody', ''));

        $errors = [];

        if ($authorName === '') {
            $errors['wbCommentAuthorName'] = Craft::t('site', 'Name is required.');
        } elseif (mb_strlen($authorName) > 100) {
            $errors['wbCommentAuthorName'] = Craft::t('site', 'Name must be 100 characters or fewer.');
        }

        if ($email === '') {
            $errors['wbEmail'] = Craft::t('site', 'Email address is required.');
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['wbEmail'] = Craft::t('site', 'Please enter a valid email address.');
        }

        if ($body === '') {
            $errors['wbCommentBody'] = Craft::t('site', 'Comment is required.');
        } elseif (mb_strlen($body) > 1000) {
            $errors['wbCommentBody'] = Craft::t('site', 'Comment must be 1000 characters or fewer.');
        }

        if (!empty($errors)) {
            return $this->_errorResponse($errors, $isAjax);
        }

        // ── Build & save comment entry ────────────────────────────────────────
        $section = Craft::$app->getEntries()->getSectionByHandle('wbComments');
        if (!$section) {
            Craft::error('wbComments section not found.', __METHOD__);
            return $this->_errorResponse(['form' => Craft::t('site', 'Comment system unavailable.')], $isAjax);
        }

        $entryType = null;
        foreach ($section->getEntryTypes() as $et) {
            if ($et->handle === 'wbComment') {
                $entryType = $et;
                break;
            }
        }

        if (!$entryType) {
            Craft::error('wbComment entry type not found.', __METHOD__);
            return $this->_errorResponse(['form' => Craft::t('site', 'Comment system unavailable.')], $isAjax);
        }

        $comment = new Entry();
        $comment->sectionId   = $section->id;
        $comment->typeId      = $entryType->id;
        $comment->siteId      = Craft::$app->getSites()->getCurrentSite()->id;
        $comment->title       = mb_substr($authorName, 0, 50) . ' — ' . date('Y-m-d H:i');
        $comment->enabled     = false;  // pending moderation

        $comment->setFieldValue('wbCommentAuthorName', $authorName);
        $comment->setFieldValue('wbEmail', $email);
        $comment->setFieldValue('wbCommentBody', $body);
        $comment->setFieldValue('wbCommentPost', [$postEntryId]);

        if (!Craft::$app->getElements()->saveElement($comment)) {
            Craft::error(
                'Failed to save comment: ' . json_encode($comment->getErrors()),
                __METHOD__
            );
            return $this->_errorResponse(
                ['form' => Craft::t('site', 'Could not save your comment. Please try again.')],
                $isAjax
            );
        }

        return $this->_successResponse($isAjax);
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    private function _successResponse(bool $isAjax): Response
    {
        if ($isAjax) {
            return $this->asJson(['success' => true]);
        }

        Craft::$app->getSession()->setFlash('wbCommentSuccess', true);

        return $this->redirectToPostedUrl();
    }

    private function _errorResponse(array $errors, bool $isAjax): Response
    {
        if ($isAjax) {
            return $this->asJson(['success' => false, 'errors' => $errors]);
        }

        $firstError = reset($errors);
        Craft::$app->getSession()->setFlash('wbCommentError', $firstError);

        return $this->redirectToPostedUrl();
    }
}
