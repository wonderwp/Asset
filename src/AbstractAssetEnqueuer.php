<?php

namespace WonderWp\Component\Asset;

abstract class AbstractAssetEnqueuer implements AssetEnqueuerInterface
{
    public $assetManager;
    /** Constructor */
    public function __construct(AssetManager $assetManager)
    {
        $this->assetManager = $assetManager;
    }
}
