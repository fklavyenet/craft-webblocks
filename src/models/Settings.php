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

    protected function defineRules(): array
    {
        return [
            [['adminEmail'], 'string'],
            [['adminEmail'], 'email'],
        ];
    }
}
