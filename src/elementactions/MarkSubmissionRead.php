<?php

namespace fklavyenet\webblocks\elementactions;

use Craft;
use craft\base\ElementAction;
use craft\elements\db\ElementQueryInterface;

/**
 * Mark Submission as Read element action.
 *
 * Sets selected wbSubmission entries' wbSubmissionStatus field to "read".
 */
class MarkSubmissionRead extends ElementAction
{
    public function getTriggerLabel(): string
    {
        return Craft::t('site', 'Mark as Read');
    }

    public function performAction(ElementQueryInterface $query): bool
    {
        $elementsService = Craft::$app->getElements();
        $user = Craft::$app->getUser()->getIdentity();
        $failCount = 0;
        $elements = $query->all();

        foreach ($elements as $element) {
            if (!$elementsService->canSave($element, $user)) {
                continue;
            }

            $element->setFieldValue('wbSubmissionStatus', 'read');

            if ($elementsService->saveElement($element) === false) {
                $failCount++;
            }
        }

        if ($failCount === count($elements)) {
            $this->setMessage(Craft::t('app', 'Could not update status due to a validation error.'));
            return false;
        }

        $this->setMessage(Craft::t('app', 'Status updated.'));
        return true;
    }
}
