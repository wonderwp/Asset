<?php

use WonderWp\Component\Asset\Asset;
use WonderWp\Component\Asset\DirectAssetEnqueuer;
use WonderWp\Component\Asset\JsonAssetExporter;
use WonderWp\Component\PluginSkeleton\Exception\ServiceNotFoundException;
use WonderWp\Component\Service\ServiceInterface;
use WonderWp\Component\PluginSkeleton\ManagerInterface;
use WonderWp\Component\DependencyInjection\Container;
use WonderWp\Component\Asset\AssetServiceInterface;
use WonderWp\Component\Asset\AssetManager;

add_action('wonderwp.loader.load', 'wwp_register_asset_definitions_towards_container', 10, 2);
add_action('wwp.abstract_manager.run', 'wwp_register_asset_service_towards_manager', 10, 2);

function wwp_register_asset_definitions_towards_container(Container $container)
{
    $container['wwp.asset.manager']       = function () {
        return AssetManager::getInstance();
    };
    $container['wwp.asset.exporterClass'] = JsonAssetExporter::class;
    $container['wwp.asset.assetClass']    = Asset::class;
    $container['wwp.asset.manifest.path'] = $container['path_root'] . '/assets.json';

    $container['wwp.asset.folder.prefix'] = './';
    $container['wwp.asset.folder.dest']   = '';
    $container['wwp.asset.folder.path']   = str_replace(trim(get_bloginfo('url'), '/'), '', str_replace(trim(network_site_url(), '/'), '', get_stylesheet_directory_uri()));

    $container['wwp.asset.enqueuer']      = function ($container) {
        $publicPath = ROOT_DIR . str_replace('.', '', $container['wwp.asset.folder.prefix']);
        return new DirectAssetEnqueuer($container['wwp.asset.manager'], $container['wwp.fileSystem'], $publicPath);
    };
}

function wwp_register_asset_service_towards_manager(ManagerInterface $manager, Container $container)
{
    // Assets
    try {
        $assetService = $manager->getService(ServiceInterface::ASSETS_SERVICE_NAME);
        if ($assetService instanceof AssetServiceInterface) {
            $assetManager = $container['wwp.asset.manager'];
            $assetManager->addAssetService($assetService);
        }
    } catch (ServiceNotFoundException $e) {
        if ($e->getServiceType() === ServiceInterface::ASSETS_SERVICE_NAME) {
            //No assets service found, nothing to do here for now
        } else {
            throw $e;
        }
    }
}
