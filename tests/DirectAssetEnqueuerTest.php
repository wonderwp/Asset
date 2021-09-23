<?php

namespace WonderWp\Component\Asset\Tests;

use WonderWp\Component\Asset\DirectAssetEnqueuer;

class DirectAssetEnqueuerTest extends AbstractEnqueuerTest
{
    /**
     * @var DirectAssetEnqueuer
     */
    private $enqueuer;

    public function setUp(): void
    {
        parent::setUp();

        // Set publicPath to test dir to resolve asset location
        $publicPath = __DIR__;

        $this->enqueuer = new DirectAssetEnqueuer($this->assetManager, new WP_Filesystem_Direct(), $publicPath, $this->wordpressAssetGatewayMock);
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
                    $this->equalTo('first-css'),
                    $this->equalTo('/fixtures/css/first.css'),
                    $this->equalTo(['second-css']),
                    $this->equalTo(null),
                ],
                [
                    $this->equalTo('second-css'),
                    $this->equalTo('/fixtures/css/second.css'),
                    $this->equalTo([]),
                    $this->equalTo(null),
                ],
                [
                    $this->equalTo('third-css'),
                    $this->equalTo('/fixtures/css/third.css'),
                    $this->equalTo([]),
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

        new DirectAssetEnqueuer($this->assetManager, new WP_Filesystem_Direct(), __DIR__, $this->wordpressAssetGatewayMock);
    }

    public function testEnqueueStyleGroupsShouldCallWordpressEnqueueStyleWithCorrectArgs()
    {
        $this->wordpressAssetGatewayMock
            ->expects($this->exactly(3))
            ->method('enqueueStyle')
            ->withConsecutive(
                [
                    'second-css'
                ],
                [
                    'third-css'
                ],
                [
                    'first-css'
                ]
            );

        $this->enqueuer->enqueueStyleGroups(['styleguide', 'admin']);
    }

    public function testEnqueueStyleGroupShouldCallWordpressEnqueueStyleWithCorrectArgs()
    {
        $this->wordpressAssetGatewayMock
            ->expects($this->once())
            ->method('enqueueStyle')
            ->with('first-css');

        $this->enqueuer->enqueueStyleGroup('admin');
    }

    public function testEnqueueStyleGroupMultipleShouldCallWordpressEnqueueStyleWithCorrectArgs()
    {
        $this->wordpressAssetGatewayMock
            ->expects($this->exactly(2))
            ->method('enqueueStyle')
            ->withConsecutive(
                [
                    'second-css'
                ],
                [
                    'third-css'
                ]
            );

        $this->enqueuer->enqueueStyleGroup('styleguide');
    }

    public function testEnqueueScriptGroupsShouldCallWordpressEnqueueScriptWithCorrectArgs()
    {
        $this->wordpressAssetGatewayMock
            ->expects($this->exactly(3))
            ->method('enqueueScript')
            ->withConsecutive(
                [
                    'second-js'
                ],
                [
                    'third-js'
                ],
                [
                    'first-js'
                ]
            );

        $this->enqueuer->enqueueScriptGroups(['styleguide', 'admin']);
    }

    public function testEnqueueScriptGroupShouldCallWordpressEnqueueScriptWithCorrectArgs()
    {
        $this->wordpressAssetGatewayMock
            ->expects($this->once())
            ->method('enqueueScript')
            ->with('first-js');

        $this->enqueuer->enqueueScriptGroup('admin');
    }

    public function testEnqueueScriptGroupMultipleShouldCallWordpressEnqueueScriptWithCorrectArgs()
    {
        $this->wordpressAssetGatewayMock
            ->expects($this->exactly(2))
            ->method('enqueueScript')
            ->withConsecutive(
                [
                    'second-js'
                ],
                [
                    'third-js'
                ]
            );

        $this->enqueuer->enqueueScriptGroup('styleguide');
    }

    public function testEnqueueStylesShouldCallWordpressEnqueueStyleWithCorrectArgs()
    {
        $this->wordpressAssetGatewayMock
            ->expects($this->exactly(2))
            ->method('enqueueStyle')
            ->withConsecutive(
                [
                    'first-css'
                ],
                [
                    'second-css'
                ]
            );

        $this->enqueuer->enqueueStyles(['first-css', 'second-css']);
    }

    public function testEnqueueStyleShouldCallWordpressEnqueueStyleWithCorrectArgs()
    {
        $this->wordpressAssetGatewayMock
            ->expects($this->once())
            ->method('enqueueStyle')
            ->with('first-css');

        $this->enqueuer->enqueueStyle('first-css');
    }

    public function testEnqueueScriptsShouldCallWordpressEnqueueScriptWithCorrectArgs()
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

        $this->enqueuer->enqueueScripts(['first-js', 'second-js']);
    }

    public function testEnqueueScriptShouldCallWordpressEnqueueScriptWithCorrectArgs()
    {
        $this->wordpressAssetGatewayMock
            ->expects($this->once())
            ->method('enqueueScript')
            ->with('first-js');

        $this->enqueuer->enqueueScript('first-js');
    }

    public function testInlineStylesShouldReturnCorrectAssetContent()
    {
        $expected = file_get_contents(__DIR__ . '/fixtures/css/first.css');
        $expected .= file_get_contents(__DIR__ . '/fixtures/css/second.css');

        $this->assertEquals($expected, $this->enqueuer->inlineStyles(['first-css', 'second-css']));
    }

    public function testInlineStyleGroupShouldReturnCorrectAssetContent()
    {
        $expected = file_get_contents(__DIR__ . '/fixtures/css/first.css');

        $this->assertEquals($expected, $this->enqueuer->inlineStyleGroup('admin'));
    }

    public function testInlineStyleGroupMultipleShouldReturnCorrectAssetContent()
    {
        $expected = file_get_contents(__DIR__ . '/fixtures/css/second.css');
        $expected .= file_get_contents(__DIR__ . '/fixtures/css/third.css');

        $this->assertEquals($expected, $this->enqueuer->inlineStyleGroup('styleguide'));
    }

    public function testInlineStyleGroupsShouldReturnCorrectAssetContent()
    {
        $expected = file_get_contents(__DIR__ . '/fixtures/css/second.css');
        $expected .= file_get_contents(__DIR__ . '/fixtures/css/third.css');
        $expected .= file_get_contents(__DIR__ . '/fixtures/css/first.css');

        $this->assertEquals($expected, $this->enqueuer->inlineStyleGroups(['styleguide', 'admin']));
    }

    public function testInlineStyleShouldReturnCorrectAssetContent()
    {
        $expected = file_get_contents(__DIR__ . '/fixtures/css/first.css');

        $this->assertEquals($expected, $this->enqueuer->inlineStyle('first-css'));
    }

    public function testInlineScriptsShouldReturnCorrectAssetContent()
    {
        $expected = file_get_contents(__DIR__ . '/fixtures/js/second.js');
        $expected .= file_get_contents(__DIR__ . '/fixtures/js/third.js');

        $this->assertEquals($expected, $this->enqueuer->inlineScripts(['second-js', 'third-js']));
    }

    public function testInlineScriptGroupShouldReturnCorrectAssetContent()
    {
        $expected = file_get_contents(__DIR__ . '/fixtures/js/first.js');

        $this->assertEquals($expected, $this->enqueuer->inlineScriptGroup('admin'));
    }

    public function testInlineScriptMultipleGroupShouldReturnCorrectAssetContent()
    {
        $expected = file_get_contents(__DIR__ . '/fixtures/js/second.js');
        $expected .= file_get_contents(__DIR__ . '/fixtures/js/third.js');

        $this->assertEquals($expected, $this->enqueuer->inlineScriptGroup('styleguide'));
    }

    public function testInlineScriptGroupsShouldReturnCorrectAssetContent()
    {
        $expected = file_get_contents(__DIR__ . '/fixtures/js/second.js');
        $expected .= file_get_contents(__DIR__ . '/fixtures/js/third.js');
        $expected .= file_get_contents(__DIR__ . '/fixtures/js/first.js');

        $this->assertEquals($expected, $this->enqueuer->inlineScriptGroups(['styleguide', 'admin']));
    }

    public function testInlineScriptShouldReturnCorrectAssetContent()
    {
        $expected = file_get_contents(__DIR__ . '/fixtures/js/first.js');

        $this->assertEquals($expected, $this->enqueuer->inlineScript('first-js'));
    }
}
