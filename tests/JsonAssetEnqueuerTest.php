<?php

namespace WonderWp\Component\Asset\Tests;

use WonderWp\Component\Asset\JsonAssetEnqueuer;

class JsonAssetEnqueuerTest extends AbstractEnqueuerTest
{
    /**
     * @var JsonAssetEnqueuer
     */
    private $enqueuer;

    protected function setUp(): void
    {
        parent::setUp();

        // Set publicPath to test dir to resolve asset location
        $publicPath = __DIR__;

        // Hard code blog url
        $blogUrl = 'http://wdf-base.test';

        $this->enqueuer = new JsonAssetEnqueuer($this->assetManager, new WP_Filesystem_Direct(), __DIR__ . '/fixtures/assets.json', $publicPath, $blogUrl, $this->wordpressAssetGatewayMock);
    }

    public function testRegisterShouldCallWordpressRegisterWithCorrectArgs()
    {
        // Register the expected behavior before create a new instance of DirectAssetEnqueuer
        // because tested method are run inside constructor (registerStyle/registerScript)
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
            ->expects($this->exactly(5))
            ->method('registerScript')
            ->withConsecutive(
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

        new JsonAssetEnqueuer($this->assetManager, new WP_Filesystem_Direct(), __DIR__ . '/fixtures/assets.json', __DIR__, 'http://wdf-base.test', $this->wordpressAssetGatewayMock);
    }

    public function testEnqueueStyleShouldCallWordpressEnqueueStyleWithCorrectArgs()
    {
        $this->wordpressAssetGatewayMock
            ->expects($this->once())
            ->method('enqueueStyle')
            ->with('styleguide');

        $this->enqueuer->enqueueStyle('styleguide');
    }

    public function testEnqueueScriptShouldCallWordpressEnqueueScriptWithCorrectArgs()
    {
        $this->wordpressAssetGatewayMock
            ->expects($this->once())
            ->method('enqueueScript')
            ->with('styleguide');

        $this->enqueuer->enqueueScript('styleguide');
    }

    public function testEnqueueStyleGroupsShouldCallWordpressEnqueueStyleWithCorrectArgs()
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

        $this->enqueuer->enqueueStyleGroups(['styleguide', 'admin']);
    }

    public function testEnqueueScriptGroupsShouldCallWordpressEnqueueScriptWithCorrectArgs()
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

        $this->enqueuer->enqueueScriptGroups(['styleguide', 'admin']);
    }

    public function testInlineStyleShouldReturnCorrectAssetContent()
    {
        $this->assertEquals(file_get_contents(__DIR__ . '/fixtures/dist/css/styleguide1629206406688.css'), $this->enqueuer->inlineStyle('styleguide'));
    }

    public function testInlineScriptShouldReturnCorrectAssetContent()
    {
        $this->assertEquals(file_get_contents(__DIR__ . '/fixtures/dist/js/styleguide1629206406688.js'), $this->enqueuer->inlineScript('styleguide'));
    }

    public function testInlineStyleGroupShouldReturnCorrectAssetContent()
    {
        $this->assertEquals(file_get_contents(__DIR__ . '/fixtures/dist/css/styleguide1629206406688.css'), $this->enqueuer->inlineStyle('styleguide'));
    }

    public function testInlineScriptGroupShouldReturnCorrectAssetContent()
    {
        $this->assertEquals(file_get_contents(__DIR__ . '/fixtures/dist/js/styleguide1629206406688.js'), $this->enqueuer->inlineScript('styleguide'));
    }

    public function testInlineStyleGroupsShouldReturnCorrectAssetContent()
    {
        $expected = file_get_contents(__DIR__.'/fixtures/dist/css/styleguide1629206406688.css');
        $expected .= file_get_contents(__DIR__.'/fixtures/dist/css/admin1629206406688.css');

        $this->assertEquals($expected, $this->enqueuer->inlineStyleGroups(['styleguide', 'admin']));
    }

    public function testInlineScriptGroupsShouldReturnCorrectAssetContent()
    {
        $expected = file_get_contents(__DIR__.'/fixtures/dist/js/styleguide1629206406688.js');
        $expected .= file_get_contents(__DIR__.'/fixtures/dist/js/admin1629206406688.js');

        $this->assertEquals($expected, $this->enqueuer->inlineScriptGroups(['styleguide', 'admin']));
    }
}
