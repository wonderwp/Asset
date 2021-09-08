<?php

namespace WonderWp\Component\Asset\Tests;

use Symfony\Component\Asset\VersionStrategy\JsonManifestVersionStrategy;
use WonderWp\Component\Asset\AssetManager;
use WonderWp\Component\Asset\AssetPackage;
use WonderWp\Component\Asset\AssetPackages;
use WonderWp\Component\Asset\PackageAssetEnqueur;
use WonderWp\Component\Asset\Tests\classes\PackageAssetService;

class PackageAssetEnqueurTest extends AbstractEnqueurTest
{
    protected function setUp(): void
    {
        parent::setUp();
        $assetManager = AssetManager::getInstance();
        $assetManager->addAssetService(new PackageAssetService());
        $this->assetManager = $assetManager;
    }

    public function getStub($packages)
    {
        $stub = $this->getMockBuilder(PackageAssetEnqueur::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['filterBlogUrl'])
            ->getMock();

        $stub->method('filterBlogUrl')->willReturn('http://wdf-base.test');
        $stub->setWordpressAssetGateway($this->wordpressAssetGatewayMock);
        $stub->assetManager = $this->assetManager;
        $stub->initEntryPath(__DIR__);
        $stub->initBlogUrl();
        $stub->initPackages($packages);

        return $stub;
    }

    public function testRegister()
    {
        $this->wordpressAssetGatewayMock
            ->expects($this->exactly(6))
            ->method('registerStyle')
            ->withConsecutive(
                [
                    $this->equalTo('admin_wwp_legacy'),
                    $this->equalTo('http://wdf-base.test/fixtures/legacy/css/admin.3401c219.css'),
                    $this->equalTo([]),
                    $this->equalTo(null),
                ],
                [
                    $this->equalTo('admin_wwp_modern'),
                    $this->equalTo('http://wdf-base.test/fixtures/modern/css/admin.38d10f31.css'),
                    $this->equalTo([]),
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
                ],
                [
                    $this->equalTo('map_wwp_legacy'),
                    $this->equalTo('http://wdf-base.test/fixtures/legacy/css/map.906640f3.css'),
                    $this->equalTo([]),
                    $this->equalTo(null),
                ],
                [
                    $this->equalTo('map_wwp_modern'),
                    $this->equalTo('http://wdf-base.test/fixtures/modern/css/map.fd3a77e9.css'),
                    $this->equalTo([]),
                    $this->equalTo(null),
                ]
            );

        $this->wordpressAssetGatewayMock
            ->expects($this->exactly(8))
            ->method('registerScript')
            ->withConsecutive(
                [
                    $this->equalTo('vendor_wwp_legacy'),
                    $this->equalTo('http://wdf-base.test/fixtures/legacy/js/vendor.dfc2b3d9.js'),
                    $this->equalTo([]),
                    $this->equalTo(null),
                ],
                [
                    $this->equalTo('vendor_wwp_modern'),
                    $this->equalTo('http://wdf-base.test/fixtures/modern/js/vendor.8e9031a4.js'),
                    $this->equalTo([]),
                    $this->equalTo(null),
                ],
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

        $stub = $this->getStub($packages);
        $stub->register();

    }

    public function testEnqueueStyles()
    {
        $legacyPackage = $this->getLegacyPackage();

        $packages = new AssetPackages(
            [$legacyPackage]
        );

        $stub = $this->getStub($packages);

        $this->wordpressAssetGatewayMock->expects($this->once())
            ->method('enqueueStyle')
            ->with($this->equalTo('styleguide_wwp_legacy'));

        $stub->enqueueStyleGroups(['styleguide']);
    }

    public function testEnqueueScript()
    {
        $legacyPackage = $this->getLegacyPackage();

        $packages = new AssetPackages(
            [$legacyPackage]
        );

        $stub = $this->getStub($packages);

        $this->wordpressAssetGatewayMock->expects($this->once())
            ->method('enqueueScript')
            ->with(
                $this->equalTo('vendor_wwp_legacy')
            );

        $stub->enqueueScriptGroups(['vendor']);
    }

    public function testEnqueueScriptWithMultipleGroups()
    {
        $legacyPackage = $this->getLegacyPackage();

        $packages = new AssetPackages(
            [$legacyPackage]
        );

        $stub = $this->getStub($packages);

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

    public function testEnqueueScriptWithMultiplePackageAndGroups()
    {
        $legacyPackage = $this->getLegacyPackage();

        $modernPackage = $this->getModernPackage();

        $packages = new AssetPackages(
            [$legacyPackage, $modernPackage]
        );

        $stub = $this->getStub($packages);

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

    public function testEnqueueStylesByAssetType()
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

        $stub = $this->getStub($packages);

        $this->wordpressAssetGatewayMock->expects($this->once())
            ->method('enqueueStyle')
            ->with(
                $this->equalTo('styleguide_wwp_legacy')
            );

        $stub->enqueueStyleGroups(['styleguide']);
    }

    public function testInlineStyle()
    {
        $legacyPackage = $this->getLegacyPackage();

        $packages = new AssetPackages(
            [$legacyPackage]
        );

        $stub = $this->getStub($packages);

        $this->assertEquals(file_get_contents(__DIR__ . '/fixtures/legacy/css/styleguide.345d3412.css'), $stub->inlineStyle('styleguide'));
    }

    public function testInlineScript()
    {
        $legacyPackage = $this->getLegacyPackage();

        $modernPackage = $this->getModernPackage();

        $packages = new AssetPackages(
            [$legacyPackage, $modernPackage]
        );

        $stub = $this->getStub($packages);

        $this->assertEquals(file_get_contents(__DIR__ . '/fixtures/legacy/js/styleguide.6a5b0114.js'), $stub->inlineScript('styleguide'));
    }

    /**
     * @return AssetPackage
     */
    private function getLegacyPackage(): AssetPackage
    {
        $legacyPackage = new AssetPackage(
            'legacy',
            new JsonManifestVersionStrategy(__DIR__ . '/fixtures/manifest.json'),
            [
                'baseUrl' => 'http://wdf-base.test',
                'basePath' => '/fixtures/legacy',
            ]
        );
        return $legacyPackage;
    }

    /**
     * @return AssetPackage
     */
    private function getModernPackage(): AssetPackage
    {
        $modernPackage = new AssetPackage(
            'modern',
            new JsonManifestVersionStrategy(__DIR__ . '/fixtures/manifest-second.json'),
            [
                'baseUrl' => 'http://wdf-base.test',
                'basePath' => '/fixtures/modern'
            ]
        );
        return $modernPackage;
    }
}
