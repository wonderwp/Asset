<?php

namespace WonderWp\Component\Asset\Tests;

use PHPUnit\Framework\TestCase;
use WonderWp\Component\Asset\AssetManager;
use WonderWp\Component\Asset\Tests\classes\ExampleAssetService;
use WonderWp\Component\Asset\WordpressAssetGateway;

abstract class AbstractEnqueurTest extends TestCase
{
    /**
     * @var mixed|\PHPUnit\Framework\MockObject\MockObject|WordpressAssetGateway
     */
    protected $wordpressAssetGatewayMock;
    /**
     * @var AssetManager
     */
    protected $assetManager;

    protected function setUp(): void
    {
        $assetManager = AssetManager::getInstance();
        $assetManager->addAssetService(new ExampleAssetService());

        $this->assetManager = $assetManager;

        $wordpressAssetGatewayMock = $this->getMockBuilder(WordpressAssetGateway::class)
            ->onlyMethods(['enqueueStyle', 'enqueueScript', 'registerStyle', 'registerScript', 'applyFilters', 'isAdmin'])
            ->getMock();

        $wordpressAssetGatewayMock->method('applyFilters')->willReturnArgument(1);
        $wordpressAssetGatewayMock->method('isAdmin')->willReturn(false);

        $this->wordpressAssetGatewayMock = $wordpressAssetGatewayMock;
    }
}
