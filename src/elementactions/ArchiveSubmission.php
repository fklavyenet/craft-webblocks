<?php

namespace fklavyenet\webblocks\elementactions;

use Craft;
use craft\base\ElementAction;
use craft\elements\db\ElementQueryInterface;

/**
 * Archive Submission element action.
 *
 * Sets selected wbSubmission entries' wbSubmissionStatus field to "archived".
 */
class ArchiveSubmission extends ElementAction
{
    public function getTriggerLabel(): string
    {
        return Craft::t('site', 'Archive');
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

            $element->setFieldValue('wbSubmissionStatus', 'archived');

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
