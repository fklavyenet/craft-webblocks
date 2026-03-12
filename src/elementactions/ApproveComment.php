<?php

namespace fklavyenet\webblocks\elementactions;

use Craft;
use craft\base\Element;
use craft\base\ElementAction;
use craft\elements\db\ElementQueryInterface;

/**
 * Approve Comment element action.
 *
 * Sets selected wbComment entries to enabled (live), making them publicly visible.
 * Appears as "Approve" in the CP element index action menu.
 */
class ApproveComment extends ElementAction
{
    public function getTriggerLabel(): string
    {
        return Craft::t('site', 'Approve');
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

            if ($element->enabled && $element->getEnabledForSite()) {
                continue;
            }

            $element->enabled = true;
            $element->setEnabledForSite(true);
            $element->setScenario(Element::SCENARIO_LIVE);

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
