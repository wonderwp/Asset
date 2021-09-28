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

        $fileVersion = $publicPath . '/fixtures/dist/version.php';
        $version = $fileVersion ? include($fileVersion) : null;

        $this->enqueuer = new JsonAssetEnqueuer(
            $this->assetManager,
            new WP_Filesystem_Direct(),
            __DIR__ . '/fixtures/assets.json',
            $publicPath,
            $blogUrl,
            $version,
            $this->wordpressAssetGatewayMock
        );
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

        $fileVersion = __DIR__ . '/fixtures/dist/version.php';
        $version = $fileVersion ? include($fileVersion) : null;

        new JsonAssetEnqueuer($this->assetManager, new WP_Filesystem_Direct(), __DIR__ . '/fixtures/assets.json', __DIR__, 'http://wdf-base.test', $version, $this->wordpressAssetGatewayMock);
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

    public function testEnqueueStyleGroupShouldCallWordpressEnqueueStyleWithCorrectArgs()
    {
        $this->wordpressAssetGatewayMock
            ->expects($this->once())
            ->method('enqueueStyle')
            ->with('styleguide');

        $this->enqueuer->enqueueStyleGroup('styleguide');
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

    public function testEnqueueScriptGroupShouldCallWordpressEnqueueScriptWithCorrectArgs()
    {
        $this->wordpressAssetGatewayMock
            ->expects($this->once())
            ->method('enqueueScript')
            ->with('styleguide');

        $this->enqueuer->enqueueScriptGroup('styleguide');
    }

    public function testEnqueueStylesShouldCallWordpressEnqueueStyleWithCorrectArgs()
    {
        $this->wordpressAssetGatewayMock
            ->expects($this->exactly(2))
            ->method('enqueueStyle')
            ->withConsecutive(
                [
                    'admin'
                ],
                [
                    'styleguide'
                ]
            );

        $this->enqueuer->enqueueStyles(['first-css', 'second-css']);
    }

    public function testEnqueueStyleShouldCallWordpressEnqueueStyleWithCorrectArgs()
    {
        $this->wordpressAssetGatewayMock
            ->expects($this->once())
            ->method('enqueueStyle')
            ->with('admin');

        $this->enqueuer->enqueueStyle('first-css');
    }

    public function testEnqueueScriptsShouldCallWordpressEnqueueScriptWithCorrectArgs()
    {
        $this->wordpressAssetGatewayMock
            ->expects($this->exactly(2))
            ->method('enqueueScript')
            ->withConsecutive(
                [
                    'admin'
                ],
                [
                    'styleguide'
                ]
            );

        $this->enqueuer->enqueueScripts(['first-js', 'second-js']);
    }

    public function testEnqueueScriptShouldCallWordpressEnqueueScriptWithCorrectArgs()
    {
        $this->wordpressAssetGatewayMock
            ->expects($this->once())
            ->method('enqueueScript')
            ->with('admin');

        $this->enqueuer->enqueueScript('first-js');
    }

    public function testInlineStylesShouldReturnCorrectAssetContent()
    {
        $expected = file_get_contents(__DIR__ . '/fixtures/dist/css/admin1629206406688.css');
        $expected .= file_get_contents(__DIR__ . '/fixtures/dist/css/styleguide1629206406688.css');

        $this->assertEquals($expected, $this->enqueuer->inlineStyles(['first-css', 'second-css']));
    }

    public function testInlineStyleGroupShouldReturnCorrectAssetContent()
    {
        $expected = file_get_contents(__DIR__ . '/fixtures/dist/css/styleguide1629206406688.css');

        $this->assertEquals($expected, $this->enqueuer->inlineStyleGroup('styleguide'));
    }

    public function testInlineStyleGroupsShouldReturnCorrectAssetContent()
    {
        $expected = file_get_contents(__DIR__ . '/fixtures/dist/css/styleguide1629206406688.css');
        $expected .= file_get_contents(__DIR__ . '/fixtures/dist/css/admin1629206406688.css');

        $this->assertEquals($expected, $this->enqueuer->inlineStyleGroups(['styleguide', 'admin']));
    }

    public function testInlineStyleShouldReturnCorrectAssetContent()
    {
        $expected = file_get_contents(__DIR__ . '/fixtures/dist/css/admin1629206406688.css');

        $this->assertEquals($expected, $this->enqueuer->inlineStyle('first-css'));
    }

    public function testInlineScriptsShouldReturnCorrectAssetContent()
    {
        $expected = file_get_contents(__DIR__ . '/fixtures/dist/js/admin1629206406688.js');
        $expected .= file_get_contents(__DIR__ . '/fixtures/dist/js/styleguide1629206406688.js');

        $this->assertEquals($expected, $this->enqueuer->inlineScripts(['first-js', 'second-js']));
    }

    public function testInlineScriptGroupShouldReturnCorrectAssetContent()
    {
        $expected = file_get_contents(__DIR__ . '/fixtures/dist/js/styleguide1629206406688.js');

        $this->assertEquals($expected, $this->enqueuer->inlineScriptGroup('styleguide'));
    }

    public function testInlineScriptGroupsShouldReturnCorrectAssetContent()
    {
        $expected = file_get_contents(__DIR__ . '/fixtures/dist/js/styleguide1629206406688.js');
        $expected .= file_get_contents(__DIR__ . '/fixtures/dist/js/admin1629206406688.js');

        $this->assertEquals($expected, $this->enqueuer->inlineScriptGroups(['styleguide', 'admin']));
    }

    public function testInlineScriptShouldReturnCorrectAssetContent()
    {
        $expected = file_get_contents(__DIR__ . '/fixtures/dist/js/styleguide1629206406688.js');

        $this->assertEquals($expected, $this->enqueuer->inlineScript('second-js'));
    }
}
