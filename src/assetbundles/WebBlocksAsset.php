<?php

namespace fklavyenet\webblocks\assetbundles;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * WebBlocksAsset
 *
 * Registers WebBlocks-specific CSS and JS for the site frontend.
 * Bootstrap remains on CDN (loaded in layout.twig).
 *
 * Craft publishes the files from sourcePath to web/cpresources/ on first
 * request and caches them — no manual copy step needed.
 */
class WebBlocksAsset extends AssetBundle
{
    public function init(): void
    {
        // Path to src/resources/ inside the plugin
        $this->sourcePath = '@fklavyenet/webblocks/resources';

        $this->css = [
            'css/wb-blocks.css',
        ];

        $this->js = [
            'js/wb-blocks.js',
        ];

        // No Craft CP dependency needed for site frontend; leave $depends empty.
        $this->depends = [];

        // Append JS to end of <body> (jsOptions position = View::POS_END)
        $this->jsOptions = ['position' => \craft\web\View::POS_END];

        parent::init();
    }
}
