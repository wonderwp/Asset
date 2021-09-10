<?php


namespace WonderWp\Component\Asset;


class PackageAssetEnqueur extends AbstractAssetEnqueuer
{
    /**
     * @var AssetPackages
     */
    private $packages;
    private $blogUrl;
    private $entryPath;
    /**
     * @var WordpressAssetGateway
     */
    private $wordpressAssetGateway;

    public function __construct(AssetManager $assetManager, $packages, $entryPath, WordpressAssetGateway $wordpressAssetGateway = null)
    {
        parent::__construct($assetManager);


        if ($wordpressAssetGateway === null) {
            $this->wordpressAssetGateway = new WordpressAssetGateway();
        } else {
            $this->wordpressAssetGateway = $wordpressAssetGateway;
        }
        $this->initEntryPath($entryPath);
        $this->initBlogUrl();
        $this->initPackages($packages);
        $this->register();
    }

    public function initEntryPath(string $entryPath)
    {
        $this->entryPath = $entryPath;
    }

    public function initBlogUrl()
    {
        $protocol = 'http';
        if (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {
            $protocol .= "s";
        }
        $protocol .= ':';
        $this->blogUrl = $this->filterBlogUrl('wwp.JsonAssetsEnqueur.blogUrl', $protocol . rtrim("//{$_SERVER['HTTP_HOST']}", '/'));
    }

    public function initPackages($packages)
    {
        $this->assetManager->callServices();

        foreach ($packages->getPackages() as $package) {
            /** @var AssetPackage $package */
            $package->setBaseUrl($this->blogUrl);
        }

        $this->packages = $packages;
    }

    public function filterBlogUrl(...$args)
    {
        return $this->wordpressAssetGateway->applyFilters(...$args);
    }

    public function register()
    {
        $styleGroups = array_reduce($this->assetManager->getDependencies('css'), function ($acc, $asset) {
            /** @var Asset $asset */
            if (!in_array($asset->concatGroup, $acc)) {
                $acc[] = $asset->concatGroup;
            }

            return $acc;
        }, []);

        foreach ($styleGroups as $group) {
            foreach ($this->packages->getPackagesBy('css') as $package) {
                /** @var $package AssetPackage */
                $this->wordpressAssetGateway->registerStyle($this->getHandleName($group, $package), $package->getUrl('css/' . $group . '.css'), [], null);

            }
        }

        $scriptGroups = array_reduce($this->assetManager->getDependencies('js'), function ($acc, $asset) {
            /** @var Asset $asset */
            if (!isset($acc[$asset->concatGroup])) {
                $acc[$asset->concatGroup] = $this->assetManager->getGroupDepencyGroups($asset->concatGroup, 'js');
            }

            return $acc;
        }, ['vendor' => []]);

        foreach ($scriptGroups as $group => $dependencies) {
            foreach ($this->packages->getPackagesBy('js') as $package) {
                $dependenciesNames = $this->computeDependencyArray($package, $dependencies);

                /** @var $package AssetPackage */
                $this->wordpressAssetGateway->registerScript($this->getHandleName($group, $package), $package->getUrl('js/' . $group . '.js'), $dependenciesNames, null);

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
            $src = $this->entryPath . $path;

            if (file_exists($src) && !$this->wordpressAssetGateway->isAdmin()) {
                return $this->wordpressAssetGateway->applyFilters('wwp.enqueur.critical.css.content', file_get_contents($src));
            }
        }

        return '';
    }

    /** @inheritDoc */
    public function inlineStyleGroup(string $groupName)
    {
        $this->inlineStyle($groupName);
    }

    /** @inheritDoc */
    public function inlineScript(string $handle)
    {
        foreach ($this->packages->getPackagesBy('critical') as $package) {
            $path = $package->getFullPath('js/' . $handle . '.js');
            $src = $this->entryPath . $path;

            if (file_exists($src)) {
                return $this->wordpressAssetGateway->applyFilters('wwp.enqueur.critical.js.content', file_get_contents($src));
            }
        }

        return '';
    }

    public function inlineScriptGroup(string $groupName)
    {
        $this->inlineScript($groupName);
    }

    /**
     * @param WordpressAssetGateway $wordpressAssetGateway
     */
    public function setWordpressAssetGateway(WordpressAssetGateway $wordpressAssetGateway): void
    {
        $this->wordpressAssetGateway = $wordpressAssetGateway;
    }

    /**
     * @param AssetPackage $package
     * @param $dependencies
     * @return array|string[]
     */
    protected function computeDependencyArray(AssetPackage $package, $dependencies): array
    {
        $dependenciesNames = array_map(function ($dependency) use ($package) {
            return $this->getHandleName($dependency, $package);
        }, $dependencies);
        return $dependenciesNames;
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
}
