<?php

/**
 * PHPUnit bootstrap for WebBlocks unit tests.
 *
 * Unit tests in this suite are pure-PHP — they do NOT require a running Craft
 * instance. The Composer autoloader is all that's needed. Tests that exercise
 * Craft-dependent classes (services that call Craft::$app, DB helpers, etc.)
 * must mock those dependencies or be placed in an integration test suite that
 * bootstraps Craft separately.
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
