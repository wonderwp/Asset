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

    public function testEnqueueStyleShouldCallWordpressEnqueueStyleWithCorrectArgs()
    {
        $this->wordpressAssetGatewayMock
            ->expects($this->once())
            ->method('enqueueStyle')
            ->with('first-css');

        $this->enqueuer->enqueueStyle('first-css');
    }

    public function testEnqueueScriptShouldCallWordpressEnqueueScriptWithCorrectArgs()
    {
        $this->wordpressAssetGatewayMock
            ->expects($this->once())
            ->method('enqueueScript')
            ->with('first-js');

        $this->enqueuer->enqueueScript('first-js');
    }

    public function testEnqueueStyleGroupsShouldCallWordpressEnqueueStyleWithCorrectArgs()
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

        $this->enqueuer->enqueueStyleGroups(['styleguide']);
    }

    public function testEnqueueScriptGroupsShouldCallWordpressEnqueueScriptWithCorrectArgs()
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

        $this->enqueuer->enqueueScriptGroups(['styleguide']);
    }

    public function testInlineStyleShouldReturnCorrectAssetContent()
    {
        $this->assertEquals(file_get_contents(__DIR__.'/fixtures/css/first.css'), $this->enqueuer->inlineStyle('first-css'));
    }

    public function testInlineScriptShouldReturnCorrectAssetContent()
    {
        $this->assertEquals(file_get_contents(__DIR__.'/fixtures/js/first.js'), $this->enqueuer->inlineScript('first-js'));
    }

    public function testInlineStyleGroupShouldReturnCorrectAssetContent()
    {
        $expected = file_get_contents(__DIR__.'/fixtures/css/second.css');
        $expected .= file_get_contents(__DIR__.'/fixtures/css/third.css');

        $this->assertEquals($expected, $this->enqueuer->inlineStyleGroup('styleguide'));
    }

    public function testInlineScriptGroupShouldReturnCorrectAssetContent()
    {
        $expected = file_get_contents(__DIR__.'/fixtures/js/second.js');
        $expected .= file_get_contents(__DIR__.'/fixtures/js/third.js');

        $this->assertEquals($expected, $this->enqueuer->inlineScriptGroup('styleguide'));
    }

    public function testInlineStyleGroupsShouldReturnCorrectAssetContent()
    {
        $expected = file_get_contents(__DIR__.'/fixtures/css/second.css');
        $expected .= file_get_contents(__DIR__.'/fixtures/css/third.css');
        $expected .= file_get_contents(__DIR__.'/fixtures/css/first.css');

        $this->assertEquals($expected, $this->enqueuer->inlineStyleGroups(['styleguide', 'admin']));
    }

    public function testInlineScriptGroupsShouldReturnCorrectAssetContent()
    {
        $expected = file_get_contents(__DIR__.'/fixtures/js/second.js');
        $expected .= file_get_contents(__DIR__.'/fixtures/js/third.js');
        $expected .= file_get_contents(__DIR__.'/fixtures/js/first.js');

        $this->assertEquals($expected, $this->enqueuer->inlineScriptGroups(['styleguide', 'admin']));
    }
}
