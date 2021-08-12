<?php

namespace WonderWp\Component\Asset\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Asset\VersionStrategy\JsonManifestVersionStrategy;
use WonderWp\Component\Asset\AssetPackage;
use WonderWp\Component\Asset\AssetPackages;
use WonderWp\Component\Asset\PackageAssetEnqueur;

class PackageAssetEnqueurTest extends TestCase
{
    public function getStub($packages)
    {
        $_SERVER["HTTP_HOST"] = 'wdf-base.test';

        $stub = $this->getMockBuilder(PackageAssetEnqueur::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['wpEnqueueStyle', 'wpEnqueueScript', 'filterBlogUrl'])
            ->getMock();

        $stub->method('filterBlogUrl')->willReturn('http://wdf-base.test');
        $stub->initBlogUrl();
        $stub->initPackages($packages);

        return $stub;
    }

    public function testEnqueueStyles()
    {
        $legacyPackage = new AssetPackage(
            'legacy',
            new JsonManifestVersionStrategy(__DIR__ . '/fixtures/manifest.json'),
            [
                'basePath' => '/app/themes/wwp_child_theme/assets/final/legacy'
            ]
        );

        $packages = new AssetPackages(
            [$legacyPackage]
        );

        $stub = $this->getStub($packages);

        $stub->expects($this->once())
            ->method('wpEnqueueStyle')
            ->with($this->equalTo('styleguide_wwp_legacy'), $this->equalTo('http://wdf-base.test/app/themes/wwp_child_theme/assets/final/legacy/css/styleguide.345d3412.css'));

        $stub->enqueueStyleGroups(['styleguide']);
    }

    public function testEnqueueScript()
    {
        $legacyPackage = new AssetPackage(
            'legacy',
            new JsonManifestVersionStrategy(__DIR__ . '/fixtures/manifest.json'),
            [
                'baseUrl' => 'http://wdf-base.test',
                'basePath' => '/app/themes/wwp_child_theme/assets/final/legacy'
            ]
        );

        $packages = new AssetPackages(
            [$legacyPackage]
        );

        $stub = $this->getStub($packages);

        $stub->expects($this->once())
            ->method('wpEnqueueScript')
            ->with(
                $this->equalTo('vendor_wwp_legacy'),
                $this->equalTo('http://wdf-base.test/app/themes/wwp_child_theme/assets/final/legacy/js/vendor.dfc2b3d9.js')
            );

        $stub->enqueueScriptGroups(['vendor']);
    }

    public function testEnqueueScriptWithMultipleGroups()
    {
        $legacyPackage = new AssetPackage(
            'legacy',
            new JsonManifestVersionStrategy(__DIR__ . '/fixtures/manifest.json'),
            [
                'baseUrl' => 'http://wdf-base.test',
                'basePath' => '/app/themes/wwp_child_theme/assets/final/legacy'
            ]
        );

        $packages = new AssetPackages(
            [$legacyPackage]
        );

        $stub = $this->getStub($packages);

        $stub->expects($this->exactly(2))
            ->method('wpEnqueueScript')
            ->withConsecutive(
                [
                    $this->equalTo('styleguide_wwp_legacy'),
                    $this->equalTo('http://wdf-base.test/app/themes/wwp_child_theme/assets/final/legacy/js/styleguide.6a5b0114.js')
                ],
                [
                    $this->equalTo('plugins_wwp_legacy'),
                    $this->equalTo('http://wdf-base.test/app/themes/wwp_child_theme/assets/final/legacy/js/plugins.6e0f0342.js')
                ]
            );

        $stub->enqueueScriptGroups(['styleguide', 'plugins']);
    }

    public function testEnqueueScriptWithMultiplePackageAndGroups()
    {
        $legacyPackage = new AssetPackage(
            'legacy',
            new JsonManifestVersionStrategy(__DIR__ . '/fixtures/manifest.json'),
            [
                'baseUrl' => 'http://wdf-base.test',
                'basePath' => '/app/themes/wwp_child_theme/assets/final/legacy'
            ]
        );

        $modernPackage = new AssetPackage(
            'modern',
            new JsonManifestVersionStrategy(__DIR__ . '/fixtures/manifest-second.json'),
            [
                'baseUrl' => 'http://wdf-base.test',
                'basePath' => '/app/themes/wwp_child_theme/assets/final/modern'
            ]
        );

        $packages = new AssetPackages(
            [$legacyPackage, $modernPackage]
        );

        $stub = $this->getStub($packages);

        $stub->expects($this->exactly(4))
            ->method('wpEnqueueScript')
            ->withConsecutive(
                [
                    $this->equalTo('styleguide_wwp_legacy'),
                    $this->equalTo('http://wdf-base.test/app/themes/wwp_child_theme/assets/final/legacy/js/styleguide.6a5b0114.js')
                ],
                [
                    $this->equalTo('styleguide_wwp_modern'),
                    $this->equalTo('http://wdf-base.test/app/themes/wwp_child_theme/assets/final/modern/js/styleguide.23cb28e5.js')
                ],
                [
                    $this->equalTo('plugins_wwp_legacy'),
                    $this->equalTo('http://wdf-base.test/app/themes/wwp_child_theme/assets/final/legacy/js/plugins.6e0f0342.js')
                ],
                [
                    $this->equalTo('plugins_wwp_modern'),
                    $this->equalTo('http://wdf-base.test/app/themes/wwp_child_theme/assets/final/modern/js/plugins.f92843f6.js')
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
                'basePath' => '/app/themes/wwp_child_theme/assets/final/legacy',
                'assetTypes' => ['js', 'css', 'critical']
            ]
        );

        $modernPackage = new AssetPackage(
            'modern',
            new JsonManifestVersionStrategy(__DIR__ . '/fixtures/manifest-second.json'),
            [
                'baseUrl' => 'http://wdf-base.test',
                'basePath' => '/app/themes/wwp_child_theme/assets/final/modern',
                'assetTypes' => ['js']
            ]
        );

        $packages = new AssetPackages(
            [$legacyPackage, $modernPackage]
        );

        $stub = $this->getStub($packages);

        $stub->expects($this->once())
            ->method('wpEnqueueStyle')
            ->with(
                $this->equalTo('styleguide_wwp_legacy'),
                $this->equalTo('http://wdf-base.test/app/themes/wwp_child_theme/assets/final/legacy/css/styleguide.345d3412.css')
            );

        $stub->enqueueStyleGroups(['styleguide']);
    }
}
