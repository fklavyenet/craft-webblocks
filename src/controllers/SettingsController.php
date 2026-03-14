<?php

namespace fklavyenet\webblocks\controllers;

use Craft;
use craft\web\Controller;
use yii\web\Response;

/**
 * Settings Controller — renders the WebBlocks Settings page in the Craft CP.
 */
class SettingsController extends Controller
{
    protected array|int|bool $allowAnonymous = false;

    public function actionIndex(): Response
    {
        $plugin = Craft::$app->getPlugins()->getPlugin('webblocks');

        return $this->renderTemplate('webblocks-cp/settings', [
            'plugin'   => $plugin,
            'settings' => $plugin->getSettings(),
        ]);
    }
}
