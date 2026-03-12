<?php

namespace fklavyenet\webblocks\elementactions;

use Craft;
use craft\base\ElementAction;
use craft\elements\db\ElementQueryInterface;

/**
 * Reject Comment element action.
 *
 * Sets selected wbComment entries to disabled, keeping them off the front end.
 * Appears as "Reject" in the CP element index action menu.
 */
class RejectComment extends ElementAction
{
    public function getTriggerLabel(): string
    {
        return Craft::t('site', 'Reject');
    }

    public function performAction(ElementQueryInterface $query): bool
    {
        $elementsService = Craft::$app->getElements();
        $user = Craft::$app->getUser()->getIdentity();
        $elements = $query->all();
        $failCount = 0;

        foreach ($elements as $element) {
            if (!$elementsService->canSave($element, $user)) {
                continue;
            }

            if (!$element->enabled) {
                continue;
            }

            $element->enabled = false;

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
