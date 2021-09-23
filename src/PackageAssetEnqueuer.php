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

        foreach ($styleGroups as $group => $dependencies) {
            foreach ($this->packages->getPackagesBy('css') as $package) {
                $dependenciesNames = $this->computeDependencyArray($package, $dependencies);

                /** @var $package AssetPackage */
                $this->wordpressAssetGateway->registerStyle(
                    $this->getHandleName($group, $package),
                    $package->getUrl('css/' . $group . '.css'),
                    $dependenciesNames,
                    null
                );

            }
        }
    }

    private function registerScripts(): void
    {
        $scriptGroups = $this->wordpressAssetGateway->applyFilters(
            'wwp.enqueuer.register.jsAssets',
            $this->assetManager->getDistinctGroupsDependencies('js'),
            $this
        );

        foreach ($scriptGroups as $group => $dependencies) {
            foreach ($this->packages->getPackagesBy('js') as $package) {
                $dependenciesNames = $this->computeDependencyArray($package, $dependencies);

                /** @var $package AssetPackage */
                $this->wordpressAssetGateway->registerScript(
                    $this->getHandleName($group, $package),
                    $package->getUrl('js/' . $group . '.js'),
                    $dependenciesNames,
                    null
                );
            }
        }
    }

    /** @inheritDoc */
    public function enqueueStyleGroup(string $groupName)
    {
        foreach ($this->packages->getPackagesBy('css') as $package) {
            /** @var AssetPackage $package */
            $this->wordpressAssetGateway->enqueueStyle($this->getHandleName($groupName, $package));
        }
    }

    /** @inheritDoc */
    public function enqueueScriptGroup(string $groupName)
    {
        foreach ($this->packages->getPackagesBy('js') as $package) {
            /** @var AssetPackage $package */
            $this->wordpressAssetGateway->enqueueScript($this->getHandleName($groupName, $package));
        }
    }

    /** @inheritDoc */
    public function enqueueStyle(string $handle)
    {
        $this->wordpressAssetGateway->enqueueStyle($handle);
    }

    /** @inheritDoc */
    public function enqueueScript(string $handle)
    {
        $this->wordpressAssetGateway->enqueueScript($handle);
    }

    /** @inheritDoc */
    public function inlineStyle(string $handle)
    {
        foreach ($this->packages->getPackagesBy('critical') as $package) {
            $path = $package->getFullPath('css/' . $handle . '.css');
            $src = $this->publicPath . $path;

            if ($this->filesystem->exists($src)) {
                return $this->wordpressAssetGateway->applyFilters(
                    'wwp.enqueuer.inline.css.content',
                    $this->filesystem->get_contents($src),
                    $this
                );
            }
        }

        return '';
    }

    /** @inheritDoc */
    public function inlineStyleGroup(string $groupName)
    {
        return $this->inlineStyle($groupName);
    }

    /** @inheritDoc */
    public function inlineScript(string $handle)
    {
        foreach ($this->packages->getPackagesBy('critical') as $package) {
            $path = $package->getFullPath('js/' . $handle . '.js');
            $src = $this->publicPath . $path;

            if ($this->filesystem->exists($src)) {
                return $this->wordpressAssetGateway->applyFilters(
                    'wwp.enqueuer.inline.js.content',
                    $this->filesystem->get_contents($src),
                    $this
                );
            }
        }

        return '';
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
    protected function computeDependencyArray(AssetPackage $package, $dependencies): array
    {
        return array_map(function ($dependency) use ($package) {
            return $this->getHandleName($dependency, $package);
        }, $dependencies);
    }

    /**
     * @param $group
     * @param AssetPackage $package
     * @return string
     */
    protected function getHandleName($group, AssetPackage $package): string
    {
        return $group . '_wwp_' . $package->getName();
    }

    /**
     * @param mixed $publicPath
     */
    public function setPublicPath($publicPath): void
    {
        $this->publicPath = $publicPath;
    }
}
