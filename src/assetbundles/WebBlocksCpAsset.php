<?php

namespace fklavyenet\webblocks\assetbundles;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * WebBlocksCpAsset
 *
 * Registers WebBlocks CP-only JavaScript (e.g. Color Theme auto-fill).
 * Loaded only on Craft CP requests.
 */
class WebBlocksCpAsset extends AssetBundle
{
    public function init(): void
    {
        $this->sourcePath = '@fklavyenet/webblocks/resources';

        $this->js = [
            'js/wb-cp.js',
        ];

        // Depend on CpAsset so our JS loads after Craft's own CP scripts
        $this->depends = [
            CpAsset::class,
        ];

        $this->jsOptions = ['position' => \craft\web\View::POS_END];

        parent::init();
    }
}
