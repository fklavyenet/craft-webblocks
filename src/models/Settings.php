<?php

namespace fklavyenet\webblocks\models;

use craft\base\Model;

/**
 * Plugin settings model.
 *
 * Stored via Craft's plugin settings (CP → Settings → Plugins → WebBlocks).
 * Accessible in Twig templates via craft.webblocks.settings.
 */
class Settings extends Model
{
    /**
     * Default recipient for wbForm admin notification emails.
     * Used as a fallback when a form's own wbFormRecipient field is empty.
     */
    public ?string $adminEmail = null;

    /**
     * Recipient for new comment notification emails.
     * When set, an email is sent every time a new comment is saved (pending moderation).
     */
    public ?string $commentNotificationEmail = null;

    /**
     * Languages to seed when running webblocks/seed.
     * 'en' is always seeded; additional values seed the corresponding extra sites.
     * Supported values: 'en', 'tr', 'de'
     *
     * @var string[]
     */
    public array $seedLanguages = ['en', 'tr', 'de'];

    /**
     * SEO <title> format string.
     * Supports {title} and {siteName} placeholders.
     * Applied when wbSeoTitle is empty; if wbSeoTitle is set it is used as-is.
     * Default: "{title} — {siteName}"
     */
    public string $seoTitleFormat = '{title} — {siteName}';

    /**
     * Google Analytics 4 Measurement ID (e.g. "G-XXXXXXXXXX").
     * When set, the gtag.js snippet is injected before </head>.
     */
    public ?string $ga4MeasurementId = null;

    /**
     * Matomo tracking URL (e.g. "https://analytics.example.com/").
     * Both matomoUrl and matomoSiteId must be set for the snippet to be injected.
     */
    public ?string $matomoUrl = null;

    /**
     * Matomo Site ID (integer, e.g. 1).
     * Both matomoUrl and matomoSiteId must be set for the snippet to be injected.
     */
    public ?int $matomoSiteId = null;

    protected function defineRules(): array
    {
        return [
            [['adminEmail'], 'string'],
            [['adminEmail'], 'email'],
            [['commentNotificationEmail'], 'string'],
            [['commentNotificationEmail'], 'email'],
            [['seedLanguages'], 'each', 'rule' => ['in', 'range' => ['en', 'tr', 'de']]],
            [['seoTitleFormat'], 'string'],
            [['seoTitleFormat'], 'required'],
            [['ga4MeasurementId'], 'string'],
            [['matomoUrl'], 'string'],
            [['matomoUrl'], 'url'],
            [['matomoSiteId'], 'integer'],
        ];
    }
}
