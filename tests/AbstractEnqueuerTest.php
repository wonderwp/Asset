<?php

namespace WonderWp\Component\Asset\Tests;

use PHPUnit\Framework\TestCase;
use WonderWp\Component\Asset\AssetManager;
use WonderWp\Component\Asset\Tests\classes\ExampleAssetService;
use WonderWp\Component\Asset\WordpressAssetGateway;

abstract class AbstractEnqueuerTest extends TestCase
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
            ->getMock();

        $wordpressAssetGatewayMock->method('applyFilters')->willReturnArgument(1);
        $wordpressAssetGatewayMock->method('isAdmin')->willReturn(false);

        $this->wordpressAssetGatewayMock = $wordpressAssetGatewayMock;
    }

    /**
     * PHPUnit 10+ replacement for withConsecutive().
     *
     * @param list<list<mixed>> $calls Expected argument lists per invocation (Constraint or scalar).
     */
    protected function consecutiveCallback(array $calls): callable
    {
        $index = 0;
        return function (...$actual) use (&$index, $calls): void {
            $this->assertArrayHasKey($index, $calls, sprintf('Unexpected extra call #%d', $index + 1));
            $expected = $calls[$index];
            $this->assertGreaterThanOrEqual(
                count($expected),
                count($actual),
                sprintf('Argument count mismatch on call #%d', $index + 1)
            );
            foreach ($expected as $i => $exp) {
                if ($exp instanceof \PHPUnit\Framework\Constraint\Constraint) {
                    $this->assertThat($actual[$i], $exp, sprintf('Call #%d arg #%d', $index + 1, $i));
                } else {
                    $this->assertSame($exp, $actual[$i], sprintf('Call #%d arg #%d', $index + 1, $i));
                }
            }
            $index++;
        };
    }
}
