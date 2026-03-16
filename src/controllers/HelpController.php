<?php

namespace fklavyenet\webblocks\controllers;

use craft\web\Controller;
use yii\web\Response;

/**
 * Help Controller — renders the WebBlocks Help page in the Craft CP.
 */
class HelpController extends Controller
{
    protected array|int|bool $allowAnonymous = false;

    public function actionIndex(): Response
    {
        $this->requireCpRequest();
        $this->requireAdmin();

        return $this->renderTemplate('webblocks-cp/help');
    }
}
