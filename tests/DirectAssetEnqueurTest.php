<?php

namespace WonderWp\Component\Asset\Tests;

use PHPUnit\Framework\TestCase;
use WonderWp\Component\Asset\AssetManager;
use WonderWp\Component\Asset\DirectAssetEnqueuer;
use WonderWp\Component\Asset\Tests\classes\ExampleAssetService;
use WonderWp\Component\Asset\WordpressAssetGateway;

class DirectAssetEnqueurTest extends AbstractEnqueurTest
{
    public function testRegister()
    {
        $this->wordpressAssetGatewayMock
            ->expects($this->exactly(3))
            ->method('registerStyle')
            ->withConsecutive(
                [
                    $this->equalTo('first-css'),
                    $this->equalTo('/fixtures/css/first.css'),
                    $this->equalTo(['styleguide']),
                    $this->equalTo(null),
                ],
                [
                    $this->equalTo('second-css'),
                    $this->equalTo('/fixtures/css/second.css'),
                    $this->equalTo(['admin']),
                    $this->equalTo(null),
                ],
                [
                    $this->equalTo('third-css'),
                    $this->equalTo('/fixtures/css/third.css'),
                    $this->equalTo(['admin']),
                    $this->equalTo(null),
                ]
            );

        $this->wordpressAssetGatewayMock
            ->expects($this->exactly(3))
            ->method('registerScript')
            ->withConsecutive(
                [
                    $this->equalTo('first-js'),
                    $this->equalTo('/fixtures/js/first.js'),
                    $this->equalTo(['second-js']),
                    $this->equalTo(null),
                ],
                [
                    $this->equalTo('second-js'),
                    $this->equalTo('/fixtures/js/second.js'),
                    $this->equalTo([]),
                    $this->equalTo(null),
                ],
                [
                    $this->equalTo('third-js'),
                    $this->equalTo('/fixtures/js/third.js'),
                    $this->equalTo([]),
                    $this->equalTo(null),
                ]
            );

        new DirectAssetEnqueuer($this->assetManager, __DIR__, $this->wordpressAssetGatewayMock);
    }

    public function testEnqueueStyle()
    {

        $this->wordpressAssetGatewayMock
            ->expects($this->once())
            ->method('enqueueStyle')
            ->with('first-css');
        $enqueur = new DirectAssetEnqueuer($this->assetManager, __DIR__, $this->wordpressAssetGatewayMock);

        $enqueur->enqueueStyle('first-css');
    }

    public function testEnqueueScript()
    {

        $this->wordpressAssetGatewayMock
            ->expects($this->once())
            ->method('enqueueScript')
            ->with('first-js');
        $enqueur = new DirectAssetEnqueuer($this->assetManager, __DIR__, $this->wordpressAssetGatewayMock);

        $enqueur->enqueueScript('first-js');
    }

    public function testEnqueueStyleGroups()
    {

        $this->wordpressAssetGatewayMock
            ->expects($this->exactly(2))
            ->method('enqueueStyle')
            ->withConsecutive(
                [
                    'first-css'
                ],
                [
                    'third-css'
                ]
            );

        $enqueur = new DirectAssetEnqueuer($this->assetManager, __DIR__, $this->wordpressAssetGatewayMock);

        $enqueur->enqueueStyleGroups(['first-group']);
    }

    public function testEnqueueScriptGroups()
    {

        $this->wordpressAssetGatewayMock
            ->expects($this->exactly(2))
            ->method('enqueueScript')
            ->withConsecutive(
                [
                    'first-js'
                ],
                [
                    'second-js'
                ]
            );

        $enqueur = new DirectAssetEnqueuer($this->assetManager, __DIR__, $this->wordpressAssetGatewayMock);

        $enqueur->enqueueScriptGroups(['first-group']);
    }

    public function testInlineStyle()
    {
        $enqueur = new DirectAssetEnqueuer($this->assetManager, __DIR__, $this->wordpressAssetGatewayMock);

        $this->assertEquals(file_get_contents(__DIR__.'/fixtures/css/first.css'), $enqueur->inlineStyle('first-css'));
    }

    public function testInlineScript()
    {
        $enqueur = new DirectAssetEnqueuer($this->assetManager, __DIR__, $this->wordpressAssetGatewayMock);

        $this->assertEquals(file_get_contents(__DIR__.'/fixtures/js/first.js'), $enqueur->inlineScript('first-js'));
    }
}
