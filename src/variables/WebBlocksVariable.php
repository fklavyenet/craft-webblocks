<?php

namespace fklavyenet\webblocks\variables;

use fklavyenet\webblocks\WebBlocks;

/**
 * Twig variable provider — accessible as {{ craft.webblocks }}.
 */
class WebBlocksVariable
{
    /**
     * Returns the plugin settings.
     * Usage: {{ craft.webblocks.settings }}
     */
    public function settings(): mixed
    {
        return WebBlocks::getInstance()->getSettings();
    }
}
