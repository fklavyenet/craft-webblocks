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
        ];
    }
}
