<?php

namespace fklavyenet\webblocks\controllers;

use Craft;
use craft\web\Controller;
use fklavyenet\webblocks\services\ComponentDiffService;
use fklavyenet\webblocks\services\ComponentMigrator;
use yii\web\Response;

/**
 * ComponentHealthController — CP health screen for the WebBlocks component
 * versioning system.
 *
 * Routes:
 *   GET  webblocks/health           — index: diff table + action buttons
 *   POST webblocks/health/dry-run   — run dry-run, redirect back with flash
 *   POST webblocks/health/migrate   — apply migrations, redirect back with flash
 */
class ComponentHealthController extends Controller
{
    protected array|int|bool $allowAnonymous = false;

    // -------------------------------------------------------------------------
    // GET — index
    // -------------------------------------------------------------------------

    /**
     * Render the component health dashboard.
     *
     * Runs a full diff and passes the result to the template.
     */
    public function actionIndex(): Response
    {
        $report = (new ComponentDiffService())->diff();

        return $this->renderTemplate('webblocks-cp/health', [
            'report' => $report,
        ]);
    }

    // -------------------------------------------------------------------------
    // POST — dry-run
    // -------------------------------------------------------------------------

    /**
     * Run a dry-run migration report and redirect back with a notice flash.
     */
    public function actionDryRun(): Response
    {
        $this->requirePostRequest();
        $this->requireAdmin();

        $report  = (new ComponentDiffService())->migrateAll(dryRun: true);
        $planned = $report['plannedActions'] ?? [];
        $count   = count(array_filter($planned, fn($a) => ($a['action'] ?? '') === 'migrate'));

        if ($count === 0) {
            Craft::$app->getSession()->setNotice(
                Craft::t('webblocks', 'Dry-run complete — no migrations pending.')
            );
        } else {
            Craft::$app->getSession()->setNotice(
                Craft::t('webblocks', '{n} migration(s) would be applied. Check console output for details.', ['n' => $count])
            );
        }

        return $this->redirect('webblocks/health');
    }

    // -------------------------------------------------------------------------
    // POST — migrate
    // -------------------------------------------------------------------------

    /**
     * Apply all pending component migrations and redirect back with a notice.
     */
    public function actionMigrate(): Response
    {
        $this->requirePostRequest();
        $this->requireAdmin();

        $report  = (new ComponentMigrator())->migrateAll(dryRun: false);
        $applied = count($report['applied'] ?? []);
        $errors  = count($report['errors']  ?? []);

        if ($errors > 0) {
            Craft::$app->getSession()->setError(
                Craft::t('webblocks', 'Migration finished with {e} error(s). Check the Craft logs for details.', ['e' => $errors])
            );
        } elseif ($applied === 0) {
            Craft::$app->getSession()->setNotice(
                Craft::t('webblocks', 'Nothing to migrate — all components are up to date.')
            );
        } else {
            Craft::$app->getSession()->setNotice(
                Craft::t('webblocks', 'Migration complete — {n} component(s) updated.', ['n' => $applied])
            );
        }

        return $this->redirect('webblocks/health');
    }
}
