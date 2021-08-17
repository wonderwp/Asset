<?php

namespace WonderWp\Component\Asset;

class JsonAssetEnqueuer extends AbstractAssetEnqueuer
{

    /** @var object */
    protected $manifest;
    /** @var string */
    protected $blogUrl;
    /** @var int */
    protected $version;
    protected $assetsFolderPrefix;

    /**
     * @param string $manifestPath
     */
    public function __construct(string $manifestPath)
    {
        parent::__construct();
        $this->manifest = json_decode(file_get_contents($manifestPath));

        $protocol = 'http';
        if (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {
            $protocol .= "s";
        }
        $protocol .= ':';
        $this->blogUrl = apply_filters('wwp.JsonAssetsEnqueur.blogUrl', $protocol . rtrim("//{$_SERVER['HTTP_HOST']}", '/'));
    }

    /** @inheritdoc */
    public function enqueueStyles(array $groupNames)
    {
        $this->enqueueStylesFn($groupNames);
    }

    /**
     * @param array $groupNames
     * @param null|string $buildType
     */
    public function enqueueStylesFn(array $groupNames, $buildType = null)
    {
        foreach ($groupNames as $group) {
            if ($this->isPropertyExistInManifest('css', $group, $buildType)) {
                $src = $this->getUrlSrcFrom('css', $group, $buildType);

                $groupName = $this->getGroupNameBy($group, $buildType);

                wp_enqueue_style($groupName, $src, [], null);
            }
        }
    }

    /** @inheritdoc */
    public function enqueueScripts(array $groupNames)
    {
        $this->enqueueScriptsFn($groupNames);
    }

    /**
     * @param array $groupNames
     * @param null|string $buildType
     */
    public function enqueueScriptsFn(array $groupNames, $buildType = null)
    {
        $availableGroups = !empty($this->manifest->js) ? array_keys(get_object_vars($this->manifest->js)) : [];

        foreach ($groupNames as $group) {
            // Note: we couldn't check for isPropertyExistInManifest
            // because js vendor is not present but should be loaded
            $dependencies = $this->computeDependencyArray($group, $availableGroups, $buildType);

            if ($group === 'vendor') {
                $src = $this->getVendorUrl($buildType);
            } else {
                $src = $this->getUrlSrcFrom('js', $group, $buildType);
            }

            $groupName = $this->getGroupNameBy($group, $buildType);

            if (!empty($src)) {
                wp_enqueue_script($groupName, $src, $dependencies, null, true);
            }
        }
    }

    protected function computeDependencyArray($groupName, $availableGroups, $buildType)
    {
        $dependencyArray = isset($this->manifest->jsDependencies->{$groupName}) ? $this->manifest->jsDependencies->{$groupName} : [];

        return $dependencyArray;
    }

    protected function getGroupNameBy(string $group, $buildType)
    {
        return $group;
    }

    /** @inheritdoc */
    public function enqueueCritical(array $groupNames)
    {
        $this->enqueueCriticalFn($groupNames);
    }

    protected function enqueueCriticalFn(array $groupNames, string $buildType = null)
    {
        foreach ($groupNames as $group) {
            if ($this->isPropertyExistInManifest('js', $group, $buildType)) {
                $src = $this->getPathSrcFrom('js', $group, $buildType);

                if (file_exists($src)) {
                    $content = apply_filters('wwp.enqueur.critical.js.content', file_get_contents($src));
                    if (!empty($content)) {
                        echo '<script id="critical-js">
                            ' . $content . '
                            </script>';
                    }
                }
            }

            if ($this->isPropertyExistInManifest('css', $group, $buildType)) {
                $src = $this->getPathSrcFrom('css', $group, $buildType);

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

    /**
     * @return int
     */
    public function getVersion()
    {
        if ($this->version === null) {
            $fileVersion = $_SERVER['DOCUMENT_ROOT'] . $this->container['wwp.asset.folder.dest'] . '/version.php';
            $this->version = file_exists($fileVersion) ? include($fileVersion) : null;
        }

        return $this->version;
    }

    /**
     * @param string $type
     * @param string $group
     * @param string|null $buildType
     * @return bool
     */
    protected function isPropertyExistInManifest(string $type, string $group, $buildType)
    {
        return property_exists($this->manifest->{$type}, $group);
    }

    /**
     * @param string $type
     * @param string $group
     * @param string|null $buildType
     * @return string
     */
    protected function getUrlSrcFrom(string $type, string $group, $buildType)
    {
        return $this->addBlogUrlTo($this->getSrcFrom($type, $group, $buildType));
    }

    /**
     * @param string $type
     * @param string $group
     * @param string|null $buildType
     * @return string
     */
    protected function getPathSrcFrom(string $type, string $group, $buildType)
    {
        return $this->addDocumentRootTo($this->getSrcFrom($type, $group, $buildType));
    }

    /**
     * @param string $type
     * @param string $group
     * @param string|null $buildType
     * @return string
     */
    protected function getSrcFrom(string $type, string $group, $buildType)
    {
        $asset = $this->manifest->site->assets_dest . '/' . $type . '/' . $group . $this->getVersion() . '.' . $type;

        return $asset;
    }

    /**
     * @param string $path
     * @return string
     */
    protected function addBlogUrlTo(string $path)
    {
        return $this->blogUrl . str_replace($this->container['wwp.asset.folder.prefix'], '', $path);
    }

    /**
     * @param string $path
     * @return string
     */
    protected function addDocumentRootTo(string $path)
    {
        return $_SERVER['DOCUMENT_ROOT'] . str_replace($this->container['wwp.asset.folder.prefix'], '', $path);
    }

    /**
     * @param string|null $buildType
     * @return string
     */
    protected function getVendorUrl($buildType)
    {
        $asset = str_replace($this->container['wwp.asset.folder.prefix'], '', $this->manifest->site->assets_dest . '/js/vendor' . $this->getVersion() . '.js');

        return $this->addBlogUrlTo(DIRECTORY_SEPARATOR . $asset);
    }
}
