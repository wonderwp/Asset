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


        if (is_null($wordpressAssetGateway)) {
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
        // Building up complete asset vision thanks to asset services
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
        $groupName = $this->wordpressAssetGateway->applyFilters(
            'wwp.enqueuer.enqueueStyleGroup.groupName',
            $groupName,
            $this
        );

        if (empty($groupName)) {
            return $this;
        }

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
        $groupName = $this->wordpressAssetGateway->applyFilters(
            'wwp.enqueuer.enqueueScriptGroup.groupName',
            $groupName,
            $this
        );

        if (empty($groupName)) {
            return $this;
        }

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
        $handle = $this->wordpressAssetGateway->applyFilters(
            'wwp.enqueuer.enqueueStyle.handle',
            $handle,
            $this
        );

        if (empty($handle)) {
            return $this;
        }

        $asset = $this->assetManager->getDependency('css', $handle);

        // This enqueur works with groups, not individual files,
        // hence retrieving the group from the file first, then enqueuing the group file.
        if ($asset) {
            $this->enqueueStyleGroup($asset->concatGroup);
        }

        return $this;
    }

    /** @inheritDoc */
    public function enqueueScript(string $handle)
    {
        $handle = $this->wordpressAssetGateway->applyFilters(
            'wwp.enqueuer.enqueueScript.handle',
            $handle,
            $this
        );

        if (empty($handle)) {
            return $this;
        }

        $asset = $this->assetManager->getDependency('js', $handle);

        // This enqueur works with groups, not individual files,
        // hence retrieving the group from the file first, then enqueuing the group file.
        if ($asset) {
            $this->enqueueScriptGroup($asset->concatGroup);
        }

        return $this;
    }

    /** @inheritDoc */
    public function inlineStyle(string $handle)
    {
        $handle = $this->wordpressAssetGateway->applyFilters(
            'wwp.enqueuer.inlineStyle.handle',
            $handle,
            $this
        );

        if (empty($handle)) {
            return '';
        }

        $asset = $this->assetManager->getDependency('css', $handle);

        // This enqueur works with groups, not individual files,
        // hence retrieving the group from the file first, then inlining the group file.
        if ($asset) {
            return $this->inlineStyleGroup($asset->concatGroup);
        }

        return '';
    }

    /** @inheritDoc */
    public function inlineStyleGroup(string $groupName)
    {
        $groupName = $this->wordpressAssetGateway->applyFilters(
            'wwp.enqueuer.inlineStyleGroup.groupName',
            $groupName,
            $this
        );

        if (empty($groupName)) {
            return '';
        }

        $packages = $this->wordpressAssetGateway->applyFilters(
            'wwp.enqueuer.inlineStyleGroup.packages',
            $this->packages->getPackages(),
            $this
        );

        $inline = '';

        foreach ($packages as $package) {
            /** @var AssetPackage $package */
            $path = $package->getFullPath('css/' . $groupName . '.css');
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
    public function inlineScript(string $handle): string
    {
        $handle = $this->wordpressAssetGateway->applyFilters(
            'wwp.enqueuer.inlineScript.handle',
            $handle,
            $this
        );

        if (empty($handle)) {
            return '';
        }

        $asset = $this->assetManager->getDependency('js', $handle);

        // This enqueur works with groups, not individual files,
        // hence retrieving the group from the file first, then inlining the group file.
        if ($asset) {
            return $this->inlineScriptGroup($asset->concatGroup);
        }


        return '';
    }

    public function inlineScriptGroup(string $groupName): string
    {
        $groupName = $this->wordpressAssetGateway->applyFilters(
            'wwp.enqueuer.inlineScriptGroup.groupName',
            $groupName,
            $this
        );

        if (empty($groupName)) {
            return '';
        }

        $packages = $this->wordpressAssetGateway->applyFilters(
            'wwp.enqueuer.inlineScriptGroup.packages',
            $this->packages->getPackages(),
            $this
        );

        $inline = '';

        foreach ($packages as $package) {
            $path = $package->getFullPath('js/' . $groupName . '.js');
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
        return 'wwp_' . $packageName . '_' . $group;
    }

    /**
     * @param mixed $publicPath
     */
    public function setPublicPath($publicPath): void
    {
        $this->publicPath = $publicPath;
    }
}
