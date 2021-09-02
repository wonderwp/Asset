<?php


namespace WonderWp\Component\Asset;


class PackageAssetEnqueur implements AssetEnqueuerInterface
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

    public function __construct($packages, $entryPath, WordpressAssetGateway $wordpressAssetGateway = null)
    {
        $this->entryPath = $entryPath;

        if ($wordpressAssetGateway === null) {
            $this->wordpressAssetGateway = new WordpressAssetGateway();
        } else {
            $this->wordpressAssetGateway = $wordpressAssetGateway;
        }

        $this->initBlogUrl();
        $this->initPackages($packages);
        $this->register();
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
        // Todo: récupérer les groupNames pour itérer et register les assets des packages
        foreach ($this->packages->getPackagesBy('css') as $package) {
            var_dump($package);die;
        }
    }

    public function enqueueStyleGroups(array $groupNames)
    {
        foreach ($groupNames as $group) {
            foreach ($this->packages->getPackagesBy('css') as $package) {
                /** @var AssetPackage $package */
                $this->wordpressAssetGateway->enqueueStyle($group . '_wwp_' . $package->getName(), $package->getUrl('css/' . $group . '.css'), [], null);
            }
        }
    }

    public function enqueueScriptGroups(array $groupNames)
    {
        foreach ($groupNames as $group) {
            foreach ($this->packages->getPackagesBy('js') as $package) {
                /** @var AssetPackage $package */
                $this->wordpressAssetGateway->enqueueScript($group . '_wwp_' . $package->getName(), $package->getUrl('js/' . $group . '.js'), [], null);
            }
        }
    }

    public function enqueueStyle(string $handle)
    {
        $this->wordpressAssetGateway->enqueueStyle($handle);
    }

    public function enqueueScript(string $handle)
    {
        $this->wordpressAssetGateway->enqueueScript($handle);
    }

    public function inlineStyle(string $handle)
    {
        foreach ($this->packages->getPackagesBy('critical') as $package) {
            $path = $package->getFullPath('js/' . $handle . '.js');
            $src = $this->entryPath . DIRECTORY_SEPARATOR . $path;
            if (file_exists($src) && !is_admin()) {
                return apply_filters('wwp.enqueur.critical.js.content', file_get_contents($src));
            }
        }

        return '';
    }

    public function inlineScript(string $handle)
    {
        foreach ($this->packages->getPackagesBy('critical') as $package) {
            $path = $package->getFullPath('css/' . $handle . '.css');
            $src = $this->entryPath . DIRECTORY_SEPARATOR . $path;

            if (file_exists($src) && !is_admin()) {
                return apply_filters('wwp.enqueur.critical.css.content', file_get_contents($src));
            }
        }

        return '';
    }

    /**
     * @param WordpressAssetGateway $wordpressAssetGateway
     */
    public function setWordpressAssetGateway(WordpressAssetGateway $wordpressAssetGateway): void
    {
        $this->wordpressAssetGateway = $wordpressAssetGateway;
    }
}
