<?php

namespace WonderWp\Component\Asset\Tests;

use WonderWp\Component\Asset\JsonAssetEnqueuer;

class JsonAssetEnqueurTest extends AbstractEnqueurTest
{
    protected function setUp(): void
    {
        parent::setUp();
        $_SERVER["HTTP_HOST"] = 'wdf-base.test';
        $_SERVER['DOCUMENT_ROOT'] = __DIR__;
    }

    public function testRegister()
    {
        $this->wordpressAssetGatewayMock
            ->expects($this->exactly(3))
            ->method('registerStyle')
            ->withConsecutive(
                [
                    $this->equalTo('styleguide'),
                    $this->equalTo('http://wdf-base.test/fixtures/dist/css/styleguide1629206406688.css'),
                    $this->equalTo([]),
                    $this->equalTo(null),
                ],
                [
                    $this->equalTo('critical'),
                    $this->equalTo('http://wdf-base.test/fixtures/dist/css/critical1629206406688.css'),
                    $this->equalTo([]),
                    $this->equalTo(null),
                ],
                [
                    $this->equalTo('admin'),
                    $this->equalTo('http://wdf-base.test/fixtures/dist/css/admin1629206406688.css'),
                    $this->equalTo([]),
                    $this->equalTo(null),
                ]
            );

        $this->wordpressAssetGatewayMock
            ->expects($this->exactly(6))
            ->method('registerScript')
            ->withConsecutive(
                [
                    $this->equalTo('vendor'),
                    $this->equalTo('http://wdf-base.test/fixtures/dist/js/vendor1629206406688.js'),
                    $this->equalTo([]),
                    $this->equalTo(null),
                ],
                [
                    $this->equalTo('styleguide'),
                    $this->equalTo('http://wdf-base.test/fixtures/dist/js/styleguide1629206406688.js'),
                    $this->equalTo([]),
                    $this->equalTo(null),
                ],
                [
                    $this->equalTo('admin'),
                    $this->equalTo('http://wdf-base.test/fixtures/dist/js/admin1629206406688.js'),
                    $this->equalTo([]),
                    $this->equalTo(null),
                ],
                [
                    $this->equalTo('plugins'),
                    $this->equalTo('http://wdf-base.test/fixtures/dist/js/plugins1629206406688.js'),
                    $this->equalTo(['styleguide']),
                    $this->equalTo(null),
                ],
                [
                    $this->equalTo('critical'),
                    $this->equalTo('http://wdf-base.test/fixtures/dist/js/critical1629206406688.js'),
                    $this->equalTo([]),
                    $this->equalTo(null),
                ],
                [
                    $this->equalTo('init'),
                    $this->equalTo('http://wdf-base.test/fixtures/dist/js/init1629206406688.js'),
                    $this->equalTo([]),
                    $this->equalTo(null),
                ]
            );

        new JsonAssetEnqueuer($this->assetManager, __DIR__ . '/fixtures/assets.json', '/fixtures/dist', '', $this->wordpressAssetGatewayMock);
    }

    public function testEnqueueStyle()
    {

        $this->wordpressAssetGatewayMock
            ->expects($this->once())
            ->method('enqueueStyle')
            ->with('styleguide');

        $enqueuer = new JsonAssetEnqueuer($this->assetManager, __DIR__ . '/fixtures/assets.json', '/fixtures/dist', '', $this->wordpressAssetGatewayMock);

        $enqueuer->enqueueStyle('styleguide');
    }

    public function testEnqueueScript()
    {

        $this->wordpressAssetGatewayMock
            ->expects($this->once())
            ->method('enqueueScript')
            ->with('styleguide');

        $enqueuer = new JsonAssetEnqueuer($this->assetManager, __DIR__ . '/fixtures/assets.json', '/fixtures/dist', '', $this->wordpressAssetGatewayMock);

        $enqueuer->enqueueScript('styleguide');
    }

    public function testEnqueueStyleGroups()
    {

        $this->wordpressAssetGatewayMock
            ->expects($this->exactly(2))
            ->method('enqueueStyle')
            ->withConsecutive(
                [
                    'styleguide'
                ],
                [
                    'admin'
                ]
            );

        $enqueuer = new JsonAssetEnqueuer($this->assetManager, __DIR__ . '/fixtures/assets.json', '/fixtures/dist', '', $this->wordpressAssetGatewayMock);

        $enqueuer->enqueueStyleGroups(['styleguide', 'admin']);
    }

    public function testEnqueueScriptGroups()
    {

        $this->wordpressAssetGatewayMock
            ->expects($this->exactly(2))
            ->method('enqueueScript')
            ->withConsecutive(
                [
                    'styleguide'
                ],
                [
                    'admin'
                ]
            );

        $enqueuer = new JsonAssetEnqueuer($this->assetManager, __DIR__ . '/fixtures/assets.json', '/fixtures/dist', '', $this->wordpressAssetGatewayMock);

        $enqueuer->enqueueScriptGroups(['styleguide', 'admin']);
    }

    public function testInlineStyle()
    {
        $enqueuer = new JsonAssetEnqueuer($this->assetManager, __DIR__ . '/fixtures/assets.json', '/fixtures/dist', '', $this->wordpressAssetGatewayMock);

        $this->assertEquals(file_get_contents(__DIR__ . '/fixtures/dist/css/styleguide1629206406688.css'), $enqueuer->inlineStyle('styleguide'));
    }

    public function testInlineScript()
    {
        $enqueuer = new JsonAssetEnqueuer($this->assetManager, __DIR__ . '/fixtures/assets.json', '/fixtures/dist', '', $this->wordpressAssetGatewayMock);

        $this->assertEquals(file_get_contents(__DIR__ . '/fixtures/dist/js/styleguide1629206406688.js'), $enqueuer->inlineScript('styleguide'));
    }
}
