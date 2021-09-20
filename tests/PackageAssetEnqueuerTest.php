<?php

namespace WonderWp\Component\Asset\Tests;

use Symfony\Component\Asset\VersionStrategy\JsonManifestVersionStrategy;
use WonderWp\Component\Asset\AssetPackage;
use WonderWp\Component\Asset\AssetPackages;
use WonderWp\Component\Asset\PackageAssetEnqueuer;

class PackageAssetEnqueuerTest extends AbstractEnqueuerTest
{
    public function getEnqueuer(AssetPackages $packages): PackageAssetEnqueuer
    {
        return new PackageAssetEnqueuer($this->assetManager, new WP_Filesystem_Direct(), $packages, __DIR__, 'http://wdf-base.test', $this->wordpressAssetGatewayMock);
    }

    public function testRegisterShouldCallWordpressRegisterWithCorrectArgs()
    {
        $this->wordpressAssetGatewayMock
            ->expects($this->exactly(4))
            ->method('registerStyle')
            ->withConsecutive(
                [
                    $this->equalTo('admin_wwp_legacy'),
                    $this->equalTo('http://wdf-base.test/fixtures/legacy/css/admin.3401c219.css'),
                    $this->equalTo(['styleguide_wwp_legacy']),
                    $this->equalTo(null),
                ],
                [
                    $this->equalTo('admin_wwp_modern'),
                    $this->equalTo('http://wdf-base.test/fixtures/modern/css/admin.38d10f31.css'),
                    $this->equalTo(['styleguide_wwp_modern']),
                    $this->equalTo(null),
                ],
                [
                    $this->equalTo('styleguide_wwp_legacy'),
                    $this->equalTo('http://wdf-base.test/fixtures/legacy/css/styleguide.345d3412.css'),
                    $this->equalTo([]),
                    $this->equalTo(null),
                ],
                [
                    $this->equalTo('styleguide_wwp_modern'),
                    $this->equalTo('http://wdf-base.test/fixtures/modern/css/styleguide.1e306c4a.css'),
                    $this->equalTo([]),
                    $this->equalTo(null),
                ]
            );

        $this->wordpressAssetGatewayMock
            ->expects($this->exactly(4))
            ->method('registerScript')
            ->withConsecutive(
                [
                    $this->equalTo('admin_wwp_legacy'),
                    $this->equalTo('http://wdf-base.test/fixtures/legacy/js/admin.8f300f60.js'),
                    $this->equalTo(['styleguide_wwp_legacy']),
                    $this->equalTo(null),
                ],
                [
                    $this->equalTo('admin_wwp_modern'),
                    $this->equalTo('http://wdf-base.test/fixtures/modern/js/admin.27f05e46.js'),
                    $this->equalTo(['styleguide_wwp_modern']),
                    $this->equalTo(null),
                ],
                [
                    $this->equalTo('styleguide_wwp_legacy'),
                    $this->equalTo('http://wdf-base.test/fixtures/legacy/js/styleguide.6a5b0114.js'),
                    $this->equalTo([]),
                    $this->equalTo(null),
                ],
                [
                    $this->equalTo('styleguide_wwp_modern'),
                    $this->equalTo('http://wdf-base.test/fixtures/modern/js/styleguide.23cb28e5.js'),
                    $this->equalTo([]),
                    $this->equalTo(null),
                ],
                [
                    $this->equalTo('plugins_wwp_legacy'),
                    $this->equalTo('http://wdf-base.test/fixtures/legacy/js/plugins.6e0f0342.js'),
                    $this->equalTo([]),
                    $this->equalTo(null),
                ],
                [
                    $this->equalTo('plugins_wwp_modern'),
                    $this->equalTo('http://wdf-base.test/fixtures/modern/js/plugins.f92843f6.js'),
                    $this->equalTo([]),
                    $this->equalTo(null),
                ]
            );

        $legacyPackage = $this->getLegacyPackage();

        $modernPackage = $this->getModernPackage();

        $packages = new AssetPackages(
            [$legacyPackage, $modernPackage]
        );

        $this->getEnqueuer($packages);
    }

    public function testEnqueueStyleGroupsShouldCallWordpressEnqueueStyleWithCorrectArgs()
    {
        $legacyPackage = $this->getLegacyPackage();

        $packages = new AssetPackages(
            [$legacyPackage]
        );

        $stub = $this->getEnqueuer($packages);

        $this->wordpressAssetGatewayMock->expects($this->once())
            ->method('enqueueStyle')
            ->with($this->equalTo('styleguide_wwp_legacy'));

        $stub->enqueueStyleGroups(['styleguide']);
    }

    public function testEnqueueScriptGroupsShouldCallWordpressEnqueueScriptWithCorrectArgs()
    {
        $legacyPackage = $this->getLegacyPackage();

        $packages = new AssetPackages(
            [$legacyPackage]
        );

        $stub = $this->getEnqueuer($packages);

        $this->wordpressAssetGatewayMock->expects($this->once())
            ->method('enqueueScript')
            ->with(
                $this->equalTo('vendor_wwp_legacy')
            );

        $stub->enqueueScriptGroups(['vendor']);
    }

    public function testEnqueueScriptWithMultipleGroupsShouldCallWordpressEnqueueScriptWithCorrectArgs()
    {
        $legacyPackage = $this->getLegacyPackage();

        $packages = new AssetPackages(
            [$legacyPackage]
        );

        $stub = $this->getEnqueuer($packages);

        $this->wordpressAssetGatewayMock->expects($this->exactly(2))
            ->method('enqueueScript')
            ->withConsecutive(
                [
                    $this->equalTo('styleguide_wwp_legacy'),
                ],
                [
                    $this->equalTo('plugins_wwp_legacy'),
                ]
            );

        $stub->enqueueScriptGroups(['styleguide', 'plugins']);
    }

    public function testEnqueueScriptWithMultiplePackageAndGroupsShouldCallWordpressEnqueueScriptWithCorrectArgs()
    {
        $legacyPackage = $this->getLegacyPackage();

        $modernPackage = $this->getModernPackage();

        $packages = new AssetPackages(
            [$legacyPackage, $modernPackage]
        );

        $stub = $this->getEnqueuer($packages);

        $this->wordpressAssetGatewayMock->expects($this->exactly(4))
            ->method('enqueueScript')
            ->withConsecutive(
                [
                    $this->equalTo('styleguide_wwp_legacy'),
                ],
                [
                    $this->equalTo('styleguide_wwp_modern'),
                ],
                [
                    $this->equalTo('plugins_wwp_legacy'),
                ],
                [
                    $this->equalTo('plugins_wwp_modern'),
                ]
            );

        $stub->enqueueScriptGroups(['styleguide', 'plugins']);
    }

    public function testEnqueueStylesByAssetTypeShouldEnqueueCorrectAsset()
    {
        $legacyPackage = new AssetPackage(
            'legacy',
            new JsonManifestVersionStrategy(__DIR__ . '/fixtures/manifest.json'),
            [
                'baseUrl' => 'http://wdf-base.test',
                'basePath' => '/fixtures/legacy',
                'assetTypes' => ['js', 'css', 'critical']
            ]
        );

        $modernPackage = new AssetPackage(
            'modern',
            new JsonManifestVersionStrategy(__DIR__ . '/fixtures/manifest-second.json'),
            [
                'baseUrl' => 'http://wdf-base.test',
                'basePath' => '/fixtures/modern',
                'assetTypes' => ['js']
            ]
        );

        $packages = new AssetPackages(
            [$legacyPackage, $modernPackage]
        );

        $stub = $this->getEnqueuer($packages);

        $this->wordpressAssetGatewayMock->expects($this->once())
            ->method('enqueueStyle')
            ->with(
                $this->equalTo('styleguide_wwp_legacy')
            );

        $stub->enqueueStyleGroups(['styleguide']);
    }

    public function testInlineStyleShouldReturnCorrectAssetContent()
    {
        $legacyPackage = $this->getLegacyPackage();

        $packages = new AssetPackages(
            [$legacyPackage]
        );

        $stub = $this->getEnqueuer($packages);

        $this->assertEquals(file_get_contents(__DIR__ . '/fixtures/legacy/css/styleguide.345d3412.css'), $stub->inlineStyle('styleguide'));
    }

    public function testInlineStyleGroupsShouldReturnCorrectAssetContent()
    {
        $legacyPackage = $this->getLegacyPackage();

        $modernPackage = $this->getModernPackage();

        $packages = new AssetPackages(
            [$legacyPackage, $modernPackage]
        );

        $stub = $this->getEnqueuer($packages);

        $expected = file_get_contents(__DIR__ . '/fixtures/legacy/css/styleguide.345d3412.css');
        $expected .= file_get_contents(__DIR__ . '/fixtures/legacy/css/admin.3401c219.css');

        $this->assertEquals($expected, $stub->inlineStyleGroups(['styleguide', 'admin']));
    }

    public function testInlineScriptShouldReturnCorrectAssetContent()
    {
        $legacyPackage = $this->getLegacyPackage();

        $modernPackage = $this->getModernPackage();

        $packages = new AssetPackages(
            [$legacyPackage, $modernPackage]
        );

        $stub = $this->getEnqueuer($packages);

        $this->assertEquals(file_get_contents(__DIR__ . '/fixtures/legacy/js/styleguide.6a5b0114.js'), $stub->inlineScript('styleguide'));
    }

    public function testInlineScriptGroupsShouldReturnCorrectAssetContent()
    {
        $legacyPackage = $this->getLegacyPackage();

        $modernPackage = $this->getModernPackage();

        $packages = new AssetPackages(
            [$legacyPackage, $modernPackage]
        );

        $stub = $this->getEnqueuer($packages);

        $expected = file_get_contents(__DIR__ . '/fixtures/legacy/js/styleguide.6a5b0114.js');
        $expected .= file_get_contents(__DIR__ . '/fixtures/legacy/js/admin.8f300f60.js');

        $this->assertEquals($expected, $stub->inlineScriptGroups(['styleguide', 'admin']));
    }

    /**
     * @return AssetPackage
     */
    private function getLegacyPackage(): AssetPackage
    {
        return new AssetPackage(
            'legacy',
            new JsonManifestVersionStrategy(__DIR__ . '/fixtures/manifest.json'),
            [
                'baseUrl' => 'http://wdf-base.test',
                'basePath' => '/fixtures/legacy',
            ]
        );
    }

    /**
     * @return AssetPackage
     */
    private function getModernPackage(): AssetPackage
    {
        return new AssetPackage(
            'modern',
            new JsonManifestVersionStrategy(__DIR__ . '/fixtures/manifest-second.json'),
            [
                'baseUrl' => 'http://wdf-base.test',
                'basePath' => '/fixtures/modern'
            ]
        );
    }
}
