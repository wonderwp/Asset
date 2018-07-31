<?php

namespace WonderWp\Component\Asset;

use WonderWp\Component\Service\AbstractService;

abstract class AbstractAssetService extends AbstractService implements AssetServiceInterface
{
    const PUBLIC_ASSETS_GROUP = 'app';
    const ADMIN_ASSETS_GROUP  = 'admin';

    protected $assets = [];

    static $assetClassName = Asset::class;

}
