<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use fklavyenet\webblocks\models\Settings;

/**
 * SettingsTest
 *
 * Tests the Settings model's validation rules using a minimal Yii2 console
 * Application bootstrap (no full Craft instance required).
 *
 * Validation rules tested:
 *   - adminEmail             — must be a valid email when provided
 *   - commentNotificationEmail — must be a valid email when provided
 *   - seedLanguages          — each value must be in ['en', 'tr', 'de']
 *   - seoTitleFormat         — required, must not be empty
 *   - matomoUrl              — must be a valid URL when provided
 *   - matomoSiteId           — must be an integer when provided
 */
class SettingsTest extends TestCase
{
    /** Yii Application is booted once for the whole test class. */
    public static function setUpBeforeClass(): void
    {
        // Load Yii class file first, then check if app already exists
        require_once __DIR__ . '/../../vendor/yiisoft/yii2/Yii.php';
        if (\Yii::$app === null) {
            new \yii\console\Application([
                'id'       => 'webblocks-test',
                'basePath' => dirname(__DIR__, 2),
            ]);
        }
    }

    /** Helper: create a valid Settings model (all rules pass as-is). */
    private function validSettings(): Settings
    {
        $s = new Settings();
        // All optional fields left null; seoTitleFormat has a default — passes
        return $s;
    }

    // -------------------------------------------------------------------------
    // Default state
    // -------------------------------------------------------------------------

    public function testDefaultSettingsAreValid(): void
    {
        $s = $this->validSettings();
        $this->assertTrue($s->validate(), 'Default Settings must be valid. Errors: ' . json_encode($s->getErrors()));
    }

    public function testDefaultSeoTitleFormat(): void
    {
        $s = new Settings();
        $this->assertSame('{title} — {siteName}', $s->seoTitleFormat);
    }

    public function testDefaultSeedLanguages(): void
    {
        $s = new Settings();
        $this->assertSame(['en', 'tr', 'de'], $s->seedLanguages);
    }

    // -------------------------------------------------------------------------
    // adminEmail
    // -------------------------------------------------------------------------

    public function testAdminEmailValidWhenNull(): void
    {
        $s = $this->validSettings();
        $s->adminEmail = null;
        $this->assertTrue($s->validate());
        $this->assertArrayNotHasKey('adminEmail', $s->getErrors());
    }

    public function testAdminEmailValidWhenCorrectFormat(): void
    {
        $s = $this->validSettings();
        $s->adminEmail = 'admin@example.com';
        $this->assertTrue($s->validate());
        $this->assertArrayNotHasKey('adminEmail', $s->getErrors());
    }

    public function testAdminEmailInvalidWhenBadFormat(): void
    {
        $s = $this->validSettings();
        $s->adminEmail = 'not-an-email';
        $this->assertFalse($s->validate());
        $this->assertArrayHasKey('adminEmail', $s->getErrors());
    }

    public function testAdminEmailInvalidWhenMissingDomain(): void
    {
        $s = $this->validSettings();
        $s->adminEmail = 'admin@';
        $this->assertFalse($s->validate());
        $this->assertArrayHasKey('adminEmail', $s->getErrors());
    }

    // -------------------------------------------------------------------------
    // commentNotificationEmail
    // -------------------------------------------------------------------------

    public function testCommentEmailValidWhenNull(): void
    {
        $s = $this->validSettings();
        $s->commentNotificationEmail = null;
        $this->assertTrue($s->validate());
    }

    public function testCommentEmailValidWhenCorrectFormat(): void
    {
        $s = $this->validSettings();
        $s->commentNotificationEmail = 'comments@example.com';
        $this->assertTrue($s->validate());
    }

    public function testCommentEmailInvalidWhenBadFormat(): void
    {
        $s = $this->validSettings();
        $s->commentNotificationEmail = 'notanemail';
        $this->assertFalse($s->validate());
        $this->assertArrayHasKey('commentNotificationEmail', $s->getErrors());
    }

    // -------------------------------------------------------------------------
    // seedLanguages
    // -------------------------------------------------------------------------

    public function testSeedLanguagesValidWithAllThree(): void
    {
        $s = $this->validSettings();
        $s->seedLanguages = ['en', 'tr', 'de'];
        $this->assertTrue($s->validate());
    }

    public function testSeedLanguagesValidWithEnOnly(): void
    {
        $s = $this->validSettings();
        $s->seedLanguages = ['en'];
        $this->assertTrue($s->validate());
    }

    public function testSeedLanguagesInvalidWithUnknownLocale(): void
    {
        $s = $this->validSettings();
        $s->seedLanguages = ['en', 'fr']; // 'fr' not in allowed range
        $this->assertFalse($s->validate());
        $this->assertArrayHasKey('seedLanguages', $s->getErrors());
    }

    public function testSeedLanguagesInvalidWithAllUnknown(): void
    {
        $s = $this->validSettings();
        $s->seedLanguages = ['xx'];
        $this->assertFalse($s->validate());
    }

    // -------------------------------------------------------------------------
    // seoTitleFormat — required
    // -------------------------------------------------------------------------

    public function testSeoTitleFormatRequiredFailsWhenEmpty(): void
    {
        $s = $this->validSettings();
        $s->seoTitleFormat = '';
        $this->assertFalse($s->validate());
        $this->assertArrayHasKey('seoTitleFormat', $s->getErrors());
    }

    public function testSeoTitleFormatValidWithCustomValue(): void
    {
        $s = $this->validSettings();
        $s->seoTitleFormat = '{title} | My Site';
        $this->assertTrue($s->validate());
    }

    // -------------------------------------------------------------------------
    // matomoUrl — URL validation
    // -------------------------------------------------------------------------

    public function testMatomoUrlValidWhenNull(): void
    {
        $s = $this->validSettings();
        $s->matomoUrl = null;
        $this->assertTrue($s->validate());
    }

    public function testMatomoUrlValidWhenWellFormed(): void
    {
        $s = $this->validSettings();
        $s->matomoUrl = 'https://analytics.example.com/';
        $this->assertTrue($s->validate());
    }

    public function testMatomoUrlInvalidWhenNotUrl(): void
    {
        $s = $this->validSettings();
        $s->matomoUrl = 'not-a-url';
        $this->assertFalse($s->validate());
        $this->assertArrayHasKey('matomoUrl', $s->getErrors());
    }

    // -------------------------------------------------------------------------
    // matomoSiteId — integer
    // -------------------------------------------------------------------------

    public function testMatomoSiteIdValidWhenNull(): void
    {
        $s = $this->validSettings();
        $s->matomoSiteId = null;
        $this->assertTrue($s->validate());
    }

    public function testMatomoSiteIdValidWhenInteger(): void
    {
        $s = $this->validSettings();
        $s->matomoSiteId = 3;
        $this->assertTrue($s->validate());
    }
}
