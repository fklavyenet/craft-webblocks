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
    // Google reCAPTCHA (used by wbForm)
    public ?string $googleReCaptchaKey = null;
    public ?string $googleReCaptchaSecret = null;

    protected function defineRules(): array
    {
        return [
            [['googleReCaptchaKey', 'googleReCaptchaSecret'], 'string'],
        ];
    }
}
