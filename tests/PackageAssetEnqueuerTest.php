<?php

namespace WonderWp\Component\Asset\Tests;

use Symfony\Component\Asset\VersionStrategy\JsonManifestVersionStrategy;
use WonderWp\Component\Asset\AssetPackage;
use WonderWp\Component\Asset\AssetPackages;
use WonderWp\Component\Asset\PackageAssetEnqueuer;

class PackageAssetEnqueuerTest extends AbstractEnqueuerTest
{
    public function testRegisterShouldCallWordpressRegisterWithCorrectArgs()
    {
        $this->wordpressAssetGatewayMock
            ->expects($this->exactly(4))
            ->method('registerStyle')
            ->withConsecutive(
                [
                    $this->equalTo('wwp_default_admin'),
                    $this->equalTo('http://wdf-base.test/fixtures/legacy/css/admin.3401c219.css'),
                    $this->equalTo(['wwp_default_styleguide']),
                    $this->equalTo(null),
                ],
                [
                    $this->equalTo('wwp_modern_admin'),
                    $this->equalTo('http://wdf-base.test/fixtures/modern/css/admin.38d10f31.css'),
                    $this->equalTo(['wwp_modern_styleguide']),
                    $this->equalTo(null),
                ],
                [
                    $this->equalTo('wwp_default_styleguide'),
                    $this->equalTo('http://wdf-base.test/fixtures/legacy/css/styleguide.345d3412.css'),
                    $this->equalTo([]),
                    $this->equalTo(null),
                ],
                [
                    $this->equalTo('wwp_modern_styleguide'),
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
                    $this->equalTo('wwp_default_admin'),
                    $this->equalTo('http://wdf-base.test/fixtures/legacy/js/admin.8f300f60.js'),
                    $this->equalTo(['wwp_default_styleguide']),
                    $this->equalTo(null),
                ],
                [
                    $this->equalTo('wwp_modern_admin'),
                    $this->equalTo('http://wdf-base.test/fixtures/modern/js/admin.27f05e46.js'),
                    $this->equalTo(['wwp_modern_styleguide']),
                    $this->equalTo(null),
                ],
                [
                    $this->equalTo('wwp_default_styleguide'),
                    $this->equalTo('http://wdf-base.test/fixtures/legacy/js/styleguide.6a5b0114.js'),
                    $this->equalTo([]),
                    $this->equalTo(null),
                ],
                [
                    $this->equalTo('wwp_modern_styleguide'),
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
            $legacyPackage,
            ['modern' => $modernPackage]
        );

        return new PackageAssetEnqueuer(
            $this->assetManager,
            new WP_Filesystem_Direct(),
            $packages,
            __DIR__,
            $this->wordpressAssetGatewayMock
        );
    }

    public function testRegisterWithoutPackagesShouldNotRegisterAnything()
    {
        $this->wordpressAssetGatewayMock
            ->expects($this->exactly(0))
            ->method('registerStyle');

        $this->wordpressAssetGatewayMock
            ->expects($this->exactly(0))
            ->method('registerScript');

        return new PackageAssetEnqueuer(
            $this->assetManager,
            new WP_Filesystem_Direct(),
            new AssetPackages(),
            __DIR__,
            $this->wordpressAssetGatewayMock
        );
    }

    public function testEnqueueStyleGroupsShouldCallWordpressEnqueueStyleWithCorrectArgs()
    {
        $stub = $this->getEnqueuer();

        $this->wordpressAssetGatewayMock->expects($this->exactly(2))
            ->method('enqueueStyle')
            ->withConsecutive(
                [
                    $this->equalTo('wwp_default_styleguide')
                ],
                [
                    $this->equalTo('wwp_default_admin')
                ]
            );

        $stub->enqueueStyleGroups(['styleguide', 'admin']);
    }

    public function testEnqueueStyleGroupShouldCallWordpressEnqueueStyleWithCorrectArgs()
    {
        $stub = $this->getEnqueuer();

        $this->wordpressAssetGatewayMock->expects($this->once())
            ->method('enqueueStyle')
            ->with($this->equalTo('wwp_default_styleguide'));

        $stub->enqueueStyleGroup('styleguide');
    }

    public function testEnqueueScriptGroupsShouldCallWordpressEnqueueScriptWithCorrectArgs()
    {
        $stub = $this->getEnqueuer();

        $this->wordpressAssetGatewayMock->expects($this->exactly(2))
            ->method('enqueueScript')
            ->withConsecutive(
                [
                    $this->equalTo('wwp_default_vendor')
                ],
                [
                    $this->equalTo('wwp_default_styleguide')
                ]
            );

        $stub->enqueueScriptGroups(['vendor', 'styleguide']);
    }

    public function testEnqueueScriptGroupShouldCallWordpressEnqueueScriptWithCorrectArgs()
    {
        $stub = $this->getEnqueuer();

        $this->wordpressAssetGatewayMock->expects($this->once())
            ->method('enqueueScript')
            ->with(
                $this->equalTo('wwp_default_vendor')
            );

        $stub->enqueueScriptGroup('vendor');
    }

    public function testEnqueueStylesShouldCallWordpressEnqueueStyleWithCorrectArgs()
    {
        $stub = $this->getEnqueuer();

        $this->wordpressAssetGatewayMock->expects($this->exactly(2))
            ->method('enqueueStyle')
            ->withConsecutive(
                [
                    $this->equalTo('wwp_default_admin')
                ],
                [
                    $this->equalTo('wwp_default_styleguide')
                ]
            );

        $stub->enqueueStyles(['first-css', 'second-css']);
    }

    public function testEnqueueStyleShouldCallWordpressEnqueueStyleWithCorrectArgs()
    {
        $stub = $this->getEnqueuer();

        $this->wordpressAssetGatewayMock->expects($this->once())
            ->method('enqueueStyle')
            ->with($this->equalTo('wwp_default_admin'));

        $stub->enqueueStyle('first-css');
    }

    public function testEnqueueScriptsShouldCallWordpressEnqueueScriptWithCorrectArgs()
    {
        $stub = $this->getEnqueuer();

        $this->wordpressAssetGatewayMock->expects($this->exactly(2))
            ->method('enqueueScript')
            ->withConsecutive(
                [
                    $this->equalTo('wwp_default_admin')
                ],
                [
                    $this->equalTo('wwp_default_styleguide')
                ]
            );

        $stub->enqueueScripts(['first-js', 'second-js']);
    }

    public function testEnqueueScriptShouldCallWordpressEnqueueScriptWithCorrectArgs()
    {
        $stub = $this->getEnqueuer();

        $this->wordpressAssetGatewayMock->expects($this->once())
            ->method('enqueueScript')
            ->with($this->equalTo('wwp_default_admin'));

        $stub->enqueueScript('first-js');
    }

    public function testInlineStylesShouldReturnCorrectAssetContent()
    {
        $stub = $this->getEnqueuer();

        $expected = file_get_contents(__DIR__ . '/fixtures/legacy/css/admin.3401c219.css');
        $expected .= file_get_contents(__DIR__ . '/fixtures/legacy/css/styleguide.345d3412.css');

        $this->assertEquals($expected, $stub->inlineStyles(['first-css', 'second-css']));
    }

    public function testInlineStyleGroupShouldReturnCorrectAssetContent()
    {
        $stub = $this->getEnqueuer();

        $expected = file_get_contents(__DIR__ . '/fixtures/legacy/css/styleguide.345d3412.css');

        $this->assertEquals($expected, $stub->inlineStyleGroup('styleguide'));
    }

    public function testInlineStyleGroupsShouldReturnCorrectAssetContent()
    {
        $stub = $this->getEnqueuer();

        $expected = file_get_contents(__DIR__ . '/fixtures/legacy/css/styleguide.345d3412.css');
        $expected .= file_get_contents(__DIR__ . '/fixtures/legacy/css/admin.3401c219.css');

        $this->assertEquals($expected, $stub->inlineStyleGroups(['styleguide', 'admin']));
    }

    public function testInlineStyleShouldReturnCorrectAssetContent()
    {
        $stub = $this->getEnqueuer();

        $expected = file_get_contents(__DIR__ . '/fixtures/legacy/css/styleguide.345d3412.css');

        $this->assertEquals($expected, $stub->inlineStyle('second-css'));
    }

    public function testInlineScriptsShouldReturnCorrectAssetContent()
    {
        $stub = $this->getEnqueuer();

        $expected = file_get_contents(__DIR__ . '/fixtures/legacy/js/styleguide.6a5b0114.js');
        $expected .= file_get_contents(__DIR__ . '/fixtures/legacy/js/admin.8f300f60.js');

        $this->assertEquals($expected, $stub->inlineScripts(['second-js', 'first-js']));
    }

    public function testInlineScriptGroupShouldReturnCorrectAssetContent()
    {
        $stub = $this->getEnqueuer();

        $expected = file_get_contents(__DIR__ . '/fixtures/legacy/js/styleguide.6a5b0114.js');

        $this->assertEquals($expected, $stub->inlineScriptGroup('styleguide'));
    }

    public function testInlineScriptGroupsShouldReturnCorrectAssetContent()
    {
        $stub = $this->getEnqueuer();

        $expected = file_get_contents(__DIR__ . '/fixtures/legacy/js/styleguide.6a5b0114.js');
        $expected .= file_get_contents(__DIR__ . '/fixtures/legacy/js/admin.8f300f60.js');

        $this->assertEquals($expected, $stub->inlineScriptGroups(['styleguide', 'admin']));
    }

    public function testInlineScriptShouldReturnCorrectAssetContent()
    {
        $stub = $this->getEnqueuer();

        $expected = file_get_contents(__DIR__ . '/fixtures/legacy/js/admin.8f300f60.js');

        $this->assertEquals($expected, $stub->inlineScript('first-js'));
    }

    public function testMultiplePackagesEnqueueStyleGroupsShouldCallWordpressEnqueueStyleWithCorrectArgs()
    {
        $stub = $this->getEnqueuer(true);

        $this->wordpressAssetGatewayMock->expects($this->exactly(2))
            ->method('enqueueStyle')
            ->withConsecutive(
                [
                    $this->equalTo('wwp_default_styleguide')
                ],
                [
                    $this->equalTo('wwp_modern_styleguide')
                ]
            );

        $stub->enqueueStyleGroups(['styleguide']);
    }

    public function testMultiplePackagesEnqueueStyleGroupShouldCallWordpressEnqueueStyleWithCorrectArgs()
    {
        $stub = $this->getEnqueuer(true);

        $this->wordpressAssetGatewayMock->expects($this->exactly(2))
            ->method('enqueueStyle')
            ->withConsecutive(
                [
                    $this->equalTo('wwp_default_styleguide')
                ],
                [
                    $this->equalTo('wwp_modern_styleguide')
                ]
            );

        $stub->enqueueStyleGroup('styleguide');
    }

    public function testMultiplePackagesEnqueueScriptGroupsShouldCallWordpressEnqueueScriptWithCorrectArgs()
    {
        $stub = $this->getEnqueuer(true);

        $this->wordpressAssetGatewayMock->expects($this->exactly(4))
            ->method('enqueueScript')
            ->withConsecutive(
                [
                    $this->equalTo('wwp_default_vendor')
                ],
                [
                    $this->equalTo('wwp_modern_vendor')
                ],
                [
                    $this->equalTo('wwp_default_styleguide')
                ],
                [
                    $this->equalTo('wwp_modern_styleguide')
                ]
            );

        $stub->enqueueScriptGroups(['vendor', 'styleguide']);
    }

    public function testMultiplePackagesEnqueueScriptGroupShouldCallWordpressEnqueueScriptWithCorrectArgs()
    {
        $stub = $this->getEnqueuer(true);

        $this->wordpressAssetGatewayMock->expects($this->exactly(2))
            ->method('enqueueScript')
            ->withConsecutive(
                [
                    $this->equalTo('wwp_default_vendor')
                ],
                [
                    $this->equalTo('wwp_modern_vendor')
                ]
            );

        $stub->enqueueScriptGroup('vendor');
    }

    public function testMultiplePackagesEnqueueStylesShouldCallWordpressEnqueueStyleWithCorrectArgs()
    {
        $stub = $this->getEnqueuer(true);

        $this->wordpressAssetGatewayMock->expects($this->exactly(4))
            ->method('enqueueStyle')
            ->withConsecutive(
                [
                    $this->equalTo('wwp_default_admin')
                ],
                [
                    $this->equalTo('wwp_modern_admin')
                ],
                [
                    $this->equalTo('wwp_default_styleguide')
                ],
                [
                    $this->equalTo('wwp_modern_styleguide')
                ]
            );

        $stub->enqueueStyles(['first-css', 'second-css']);
    }

    public function testMultiplePackagesEnqueueStyleShouldCallWordpressEnqueueStyleWithCorrectArgs()
    {
        $stub = $this->getEnqueuer(true);

        $this->wordpressAssetGatewayMock->expects($this->exactly(2))
            ->method('enqueueStyle')
            ->withConsecutive(
                [
                    $this->equalTo('wwp_default_admin')
                ],
                [
                    $this->equalTo('wwp_modern_admin')
                ]
            );

        $stub->enqueueStyle('first-css');
    }

    public function testMultiplePackagesEnqueueScriptsShouldCallWordpressEnqueueScriptWithCorrectArgs()
    {
        $stub = $this->getEnqueuer(true);

        $this->wordpressAssetGatewayMock->expects($this->exactly(4))
            ->method('enqueueScript')
            ->withConsecutive(
                [
                    $this->equalTo('wwp_default_admin')
                ],
                [
                    $this->equalTo('wwp_modern_admin')
                ],
                [
                    $this->equalTo('wwp_default_styleguide')
                ],
                [
                    $this->equalTo('wwp_modern_styleguide')
                ]
            );

        $stub->enqueueScripts(['first-js', 'second-js']);
    }

    public function testMultiplePackagesEnqueueScriptShouldCallWordpressEnqueueScriptWithCorrectArgs()
    {
        $stub = $this->getEnqueuer(true);

        $this->wordpressAssetGatewayMock->expects($this->exactly(2))
            ->method('enqueueScript')
            ->withConsecutive(
                [
                    $this->equalTo('wwp_default_admin')
                ],
                [
                    $this->equalTo('wwp_modern_admin')
                ]
            );

        $stub->enqueueScript('first-js');
    }

    public function testMultiplePackagesInlineStylesShouldReturnCorrectAssetContent()
    {
        $stub = $this->getEnqueuer(true);

        $expected = file_get_contents(__DIR__ . '/fixtures/legacy/css/admin.3401c219.css');
        $expected .= file_get_contents(__DIR__ . '/fixtures/modern/css/admin.38d10f31.css');
        $expected .= file_get_contents(__DIR__ . '/fixtures/legacy/css/styleguide.345d3412.css');
        $expected .= file_get_contents(__DIR__ . '/fixtures/modern/css/styleguide.1e306c4a.css');

        $this->assertEquals($expected, $stub->inlineStyles(['first-css', 'second-css']));
    }

    public function testMultiplePackagesInlineStyleGroupShouldReturnCorrectAssetContent()
    {
        $stub = $this->getEnqueuer(true);

        $expected = file_get_contents(__DIR__ . '/fixtures/legacy/css/styleguide.345d3412.css');
        $expected .= file_get_contents(__DIR__ . '/fixtures/modern/css/styleguide.1e306c4a.css');

        $this->assertEquals($expected, $stub->inlineStyleGroup('styleguide'));
    }

    public function testMultiplePackagesInlineStyleGroupsShouldReturnCorrectAssetContent()
    {
        $stub = $this->getEnqueuer(true);

        $expected = file_get_contents(__DIR__ . '/fixtures/legacy/css/styleguide.345d3412.css');
        $expected .= file_get_contents(__DIR__ . '/fixtures/modern/css/styleguide.1e306c4a.css');
        $expected .= file_get_contents(__DIR__ . '/fixtures/legacy/css/admin.3401c219.css');
        $expected .= file_get_contents(__DIR__ . '/fixtures/modern/css/admin.38d10f31.css');

        $this->assertEquals($expected, $stub->inlineStyleGroups(['styleguide', 'admin']));
    }

    public function testMultiplePackagesInlineStyleShouldReturnCorrectAssetContent()
    {
        $stub = $this->getEnqueuer(true);

        $expected = file_get_contents(__DIR__ . '/fixtures/legacy/css/styleguide.345d3412.css');
        $expected .= file_get_contents(__DIR__ . '/fixtures/modern/css/styleguide.1e306c4a.css');

        $this->assertEquals($expected, $stub->inlineStyle('second-css'));
    }

    public function testMultiplePackagesInlineScriptsShouldReturnCorrectAssetContent()
    {
        $stub = $this->getEnqueuer(true);

        $expected = file_get_contents(__DIR__ . '/fixtures/legacy/js/styleguide.6a5b0114.js');
        $expected .= file_get_contents(__DIR__ . '/fixtures/modern/js/styleguide.23cb28e5.js');
        $expected .= file_get_contents(__DIR__ . '/fixtures/legacy/js/admin.8f300f60.js');
        $expected .= file_get_contents(__DIR__ . '/fixtures/modern/js/admin.27f05e46.js');

        $this->assertEquals($expected, $stub->inlineScripts(['second-js', 'first-js']));
    }

    public function testMultiplePackagesInlineScriptGroupShouldReturnCorrectAssetContent()
    {
        $stub = $this->getEnqueuer(true);

        $expected = file_get_contents(__DIR__ . '/fixtures/legacy/js/styleguide.6a5b0114.js');
        $expected .= file_get_contents(__DIR__ . '/fixtures/modern/js/styleguide.23cb28e5.js');

        $this->assertEquals($expected, $stub->inlineScriptGroup('styleguide'));
    }

    public function testMultiplePackagesInlineScriptGroupsShouldReturnCorrectAssetContent()
    {
        $stub = $this->getEnqueuer(true);

        $expected = file_get_contents(__DIR__ . '/fixtures/legacy/js/styleguide.6a5b0114.js');
        $expected .= file_get_contents(__DIR__ . '/fixtures/modern/js/styleguide.23cb28e5.js');

        $expected .= file_get_contents(__DIR__ . '/fixtures/legacy/js/admin.8f300f60.js');
        $expected .= file_get_contents(__DIR__ . '/fixtures/modern/js/admin.27f05e46.js');

        $this->assertEquals($expected, $stub->inlineScriptGroups(['styleguide', 'admin']));
    }

    public function testMultiplePackagesInlineScriptShouldReturnCorrectAssetContent()
    {
        $stub = $this->getEnqueuer(true);

        $expected = file_get_contents(__DIR__ . '/fixtures/legacy/js/admin.8f300f60.js');
        $expected .= file_get_contents(__DIR__ . '/fixtures/modern/js/admin.27f05e46.js');

        $this->assertEquals($expected, $stub->inlineScript('first-js'));
    }

    public function testEnqueueStyleGroupsWithoutPackagesShouldNotCallEnqueue()
    {
        $stub = $this->getEnqueuerWithoutPackages();

        $this->wordpressAssetGatewayMock->expects($this->exactly(0))
            ->method('enqueueStyle');

        $stub->enqueueStyleGroups(['styleguide', 'admin']);
    }

    public function testEnqueueScriptGroupsWithoutPackagesShouldNotCallEnqueue()
    {
        $stub = $this->getEnqueuerWithoutPackages();

        $this->wordpressAssetGatewayMock->expects($this->exactly(0))
            ->method('enqueueScript');

        $stub->enqueueScriptGroups(['styleguide', 'admin']);
    }

    public function testInlineStyleWithoutPackagesShouldReturnEmptyString()
    {
        $stub = $this->getEnqueuerWithoutPackages();

        $this->assertEquals('', $stub->inlineStyle('second-css'));
    }

    public function testInlineStyleGroupWithoutPackagesShouldReturnEmptyString()
    {
        $stub = $this->getEnqueuerWithoutPackages();

        $this->assertEquals('', $stub->inlineStyleGroup('styleguide'));
    }

    public function testInlineScriptWithoutPackagesShouldReturnEmptyString()
    {
        $stub = $this->getEnqueuerWithoutPackages();

        $this->assertEquals('', $stub->inlineScript('first-js'));
    }

    public function testInlineScriptGroupWithoutPackagesShouldReturnEmptyString()
    {
        $stub = $this->getEnqueuerWithoutPackages();

        $this->assertEquals('', $stub->inlineScriptGroup('styleguide'));
    }

    /**
     * @return AssetPackage
     */
    private function getLegacyPackage(): AssetPackage
    {
        return new AssetPackage(
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
            new JsonManifestVersionStrategy(__DIR__ . '/fixtures/manifest-second.json'),
            [
                'baseUrl' => 'http://wdf-base.test',
                'basePath' => '/fixtures/modern',
            ]
        );
    }

    /**
     * @param bool $withModern
     * @return PackageAssetEnqueuer
     * @throws \WonderWp\Component\Asset\Exception\PackageReservedNameException
     */
    private function getEnqueuer(bool $withModern = false): PackageAssetEnqueuer
    {
        $legacyPackage = $this->getLegacyPackage();

        $packages = new AssetPackages($legacyPackage);

        if ($withModern) {
            $modernPackage = $this->getModernPackage();

            $packages->addPackage('modern', $modernPackage);
        }

        return new PackageAssetEnqueuer(
            $this->assetManager,
            new WP_Filesystem_Direct(),
            $packages,
            __DIR__,
            $this->wordpressAssetGatewayMock
        );
    }

    public function getEnqueuerWithoutPackages()
    {
        return new PackageAssetEnqueuer(
            $this->assetManager,
            new WP_Filesystem_Direct(),
            new AssetPackages(),
            __DIR__,
            $this->wordpressAssetGatewayMock
        );
    }
}
