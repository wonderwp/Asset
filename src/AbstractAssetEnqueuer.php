<?php

namespace WonderWp\Component\Asset;

use WonderWp\Component\DependencyInjection\Container;

abstract class AbstractAssetEnqueuer implements AssetEnqueuerInterface
{
    /** @var Container */
    protected $container;

    /** Constructor */
    public function __construct()
    {
        $this->container = Container::getInstance();
    }
}
