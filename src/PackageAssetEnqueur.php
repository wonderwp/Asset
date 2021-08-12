<?php


namespace WonderWp\Component\Asset;


class PackageAssetEnqueur implements AssetEnqueuerInterface
{
    /**
     * @var AssetPackages
     */
    private $packages;
    private $blogUrl;

    public function __construct($packages)
    {
        $this->initBlogUrl();

        $this->initPackages($packages);
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
        return apply_filters(...$args);
    }

    public function enqueueStyleGroups(array $groupNames)
    {
        foreach ($groupNames as $group) {
            foreach ($this->packages->getPackagesBy('css') as $package) {
                /** @var AssetPackage $package */
                $this->wpEnqueueStyle($group . '_wwp_' . $package->getName(), $package->getUrl('css/' . $group . '.css'), [], null);

            }
        }
    }

    public function wpEnqueueStyle(...$args)
    {
        \wp_enqueue_style(...$args);
    }

    public function enqueueScriptGroups(array $groupNames)
    {
        foreach ($groupNames as $group) {
            foreach ($this->packages->getPackagesBy('js') as $package) {
                /** @var AssetPackage $package */
                $this->wpEnqueueScript($group . '_wwp_' . $package->getName(), $package->getUrl('js/' . $group . '.js'), [], null);

            }
        }
    }

    public function wpEnqueueScript(...$args)
    {
        \wp_enqueue_script(...$args);
    }

    public function enqueueCriticalGroups(array $groupNames)
    {
        foreach ($groupNames as $group) {
            foreach ($this->packages->getPackagesBy('critical') as $package) {
                $src = $package->getUrl('js/' . $group . '.js');

                if (file_exists($src)) {
                    $content = apply_filters('wwp.enqueur.critical.js.content', file_get_contents($src));
                    if (!empty($content)) {
                        echo '<script id="critical-js">
                            ' . $content . '
                            </script>';
                    }
                }
            }

            foreach ($this->packages->getPackagesBy('critical') as $package) {
                $src = $package->getUrl('css/' . $group . '.css');

                if (file_exists($src) && !is_admin()) {
                    $content = apply_filters('wwp.enqueur.critical.css.content', file_get_contents($src));
                    if (!empty($content)) {
                        echo '<style id="critical-css">
                        ' . $content . '
                        </style>';
                    }
                }
            }
        }
    }

    public function enqueueStyle(string $handle)
    {
        // TODO: Implement enqueueStyle() method.
    }

    public function enqueueScript(string $handle)
    {
        // TODO: Implement enqueueScript() method.
    }

    public function enqueueCritical(string $handle)
    {
        // TODO: Implement enqueueCritical() method.
    }
}
