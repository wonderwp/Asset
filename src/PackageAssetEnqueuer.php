<?php

namespace WonderWp\Component\Asset;

class PackageAssetEnqueuer extends AbstractAssetEnqueuer
{
    /**
     * @var AssetPackages
     */
    private $packages;
    private $publicPath;
    /** @var WordpressAssetGateway */
    private $wordpressAssetGateway;
    /** @var \WP_Filesystem_Base */
    private $filesystem;

    /**
     * @param AssetManager $assetManager
     * @param \WP_Filesystem_Base $filesystem
     * @param AssetPackages $packages
     * @param string $publicPath
     * @param WordpressAssetGateway|null $wordpressAssetGateway
     */
    public function __construct(AssetManager $assetManager, $filesystem, AssetPackages $packages, string $publicPath, WordpressAssetGateway $wordpressAssetGateway = null)
    {
        parent::__construct($assetManager);


        if ($wordpressAssetGateway === null) {
            $this->wordpressAssetGateway = new WordpressAssetGateway();
        } else {
            $this->wordpressAssetGateway = $wordpressAssetGateway;
        }

        $this->filesystem = $filesystem;
        $this->packages = $packages;
        $this->setPublicPath($publicPath);
        $this->register();
    }

    public function register()
    {
        $this->assetManager->callServices();

        $this->registerStyles();
        $this->registerScripts();
    }

    private function registerStyles(): void
    {
        $styleGroups = $this->wordpressAssetGateway->applyFilters(
            'wwp.enqueuer.register.cssAssets',
            $this->assetManager->getDistinctGroupsDependencies('css'),
            $this
        );

        $cssToRegister = [];

        foreach ($styleGroups as $group => $dependencies) {
            foreach ($this->packages->getPackages() as $packageName => $package) {
                $dependenciesNames = $this->computeDependencyArray($packageName, $dependencies);

                /** @var $package AssetPackage */
                $cssToRegister[$this->getHandleName($group, $packageName)] = [
                    'handle' => $this->getHandleName($group, $packageName),
                    'src' => $package->getUrl('css/' . $group . '.css'),
                    'deps' => $dependenciesNames,
                    'ver' => null,
                    'media' => null
                ];
            }
        }

        $cssToRegister = $this->wordpressAssetGateway->applyFilters('wwp.enqueuer.register.cssToRegister', $cssToRegister, $this);

        foreach ($cssToRegister as $cssAsset) {
            $this->wordpressAssetGateway->registerStyle($cssAsset['handle'], $cssAsset['src'], $cssAsset['deps'], $cssAsset['ver'], $cssAsset['media']);
        }
    }

    private function registerScripts(): void
    {
        $scriptGroups = $this->wordpressAssetGateway->applyFilters(
            'wwp.enqueuer.register.jsAssets',
            $this->assetManager->getDistinctGroupsDependencies('js'),
            $this
        );

        $jsToRegister = [];

        foreach ($scriptGroups as $group => $dependencies) {
            foreach ($this->packages->getPackages() as $packageName => $package) {
                $dependenciesNames = $this->computeDependencyArray($packageName, $dependencies);

                /** @var $package AssetPackage */
                $jsToRegister[$this->getHandleName($group, $packageName)] = [
                    'handle' => $this->getHandleName($group, $packageName),
                    'src' => $package->getUrl('js/' . $group . '.js'),
                    'deps' => $dependenciesNames,
                    'ver' => null,
                    'in_footer' => true
                ];
            }
        }

        $jsToRegister = $this->wordpressAssetGateway->applyFilters('wwp.enqueuer.register.jsToRegister', $jsToRegister, $this);

        foreach ($jsToRegister as $jsAsset) {
            $this->wordpressAssetGateway->registerScript($jsAsset['handle'], $jsAsset['src'], $jsAsset['deps'], $jsAsset['ver'], $jsAsset['in_footer']);
        }
    }

    /** @inheritDoc */
    public function enqueueStyleGroup(string $groupName)
    {
        $packages = $this->wordpressAssetGateway->applyFilters(
            'wwp.enqueuer.enqueueStyleGroup.packages',
            $this->packages->getPackages(),
            $this
        );

        foreach ($packages as $packageName => $package) {
            /** @var AssetPackage $package */
            $this->wordpressAssetGateway->enqueueStyle($this->getHandleName($groupName, $packageName));
        }

        return $this;
    }

    /** @inheritDoc */
    public function enqueueScriptGroup(string $groupName)
    {
        $packages = $this->wordpressAssetGateway->applyFilters(
            'wwp.enqueuer.enqueueScriptGroup.packages',
            $this->packages->getPackages(),
            $this
        );

        foreach ($packages as $packageName => $package) {
            /** @var AssetPackage $package */
            $this->wordpressAssetGateway->enqueueScript($this->getHandleName($groupName, $packageName));
        }

        return $this;
    }

    /** @inheritDoc */
    public function enqueueStyle(string $handle)
    {
        $this->wordpressAssetGateway->enqueueStyle($handle);

        return $this;
    }

    /** @inheritDoc */
    public function enqueueScript(string $handle)
    {
        $this->wordpressAssetGateway->enqueueScript($handle);

        return $this;
    }

    /** @inheritDoc */
    public function inlineStyle(string $handle)
    {
        $packages = $this->wordpressAssetGateway->applyFilters(
            'wwp.enqueuer.inlineStyle.packages',
            $this->packages->getPackages(),
            $this
        );

        $inline = '';

        foreach ($packages as $package) {
            /** @var AssetPackage $package */
            $path = $package->getFullPath('css/' . $handle . '.css');
            $src = $this->publicPath . $path;

            if ($this->filesystem->exists($src)) {
                $inline .= $this->wordpressAssetGateway->applyFilters(
                    'wwp.enqueuer.inline.css.content',
                    $this->filesystem->get_contents($src),
                    $this
                );
            }
        }

        return $inline;
    }

    /** @inheritDoc */
    public function inlineStyleGroup(string $groupName)
    {
        return $this->inlineStyle($groupName);
    }

    /** @inheritDoc */
    public function inlineScript(string $handle)
    {
        $packages = $this->wordpressAssetGateway->applyFilters(
            'wwp.enqueuer.inlineScript.packages',
            $this->packages->getPackages(),
            $this
        );

        $inline = '';

        foreach ($packages as $package) {
            $path = $package->getFullPath('js/' . $handle . '.js');
            $src = $this->publicPath . $path;

            if ($this->filesystem->exists($src)) {
                $inline .= $this->wordpressAssetGateway->applyFilters(
                    'wwp.enqueuer.inline.js.content',
                    $this->filesystem->get_contents($src),
                    $this
                );
            }
        }

        return $inline;
    }

    public function inlineScriptGroup(string $groupName)
    {
        return $this->inlineScript($groupName);
    }

    /**
     * @param AssetPackage $package
     * @param $dependencies
     * @return array|string[]
     */
    protected function computeDependencyArray(string $packageName, $dependencies): array
    {
        return array_map(function ($dependency) use ($packageName) {
            return $this->getHandleName($dependency, $packageName);
        }, $dependencies);
    }

    /**
     * @param $group
     * @param AssetPackage $package
     * @return string
     */
    protected function getHandleName($group, string $packageName): string
    {
        return $group . '_wwp_' . $packageName;
    }

    /**
     * @param mixed $publicPath
     */
    public function setPublicPath($publicPath): void
    {
        $this->publicPath = $publicPath;
    }
}
