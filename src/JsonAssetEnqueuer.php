<?php

namespace WonderWp\Component\Asset;

class JsonAssetEnqueuer extends AbstractAssetEnqueuer
{
    const BUILD_TYPE_LEGACY = 'legacy';
    const BUILD_TYPE_MODERN = 'modern';

    /** @var object */
    protected $manifest;
    /** @var string */
    protected $blogUrl;
    /** @var int */
    protected $version;
    protected $assetsFolderPrefix;
    /** @var object[] */
    protected $entrypointsFiles = [];
    /** @var object[] */
    protected $versionFiles = [];

    /**
     * @param string $manifestPath
     * @param string $distPath
     */
    public function __construct(string $manifestPath)
    {
        parent::__construct();

        $this->manifest = json_decode(file_get_contents($manifestPath));
        $this->checkIfShouldBeUsingDifferencialServing($this->getDistPath());

        $protocol = 'http';
        if (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {
            $protocol .= "s";
        }
        $protocol .= ':';
        $this->blogUrl = apply_filters('wwp.JsonAssetsEnqueur.blogUrl', $protocol . rtrim("//{$_SERVER['HTTP_HOST']}", '/'));
    }

    private function getDistPath()
    {
        return $_SERVER['DOCUMENT_ROOT'] . $this->container['wwp.asset.folder.dest'];
    }

    /** @inheritdoc */
    public function enqueueStyles(array $groupNames)
    {
        if ($this->shouldUseDifferencialServing()) {
            $this->enqueueStylesFn($groupNames, self::BUILD_TYPE_LEGACY);
        } else {
            $this->enqueueStylesFn($groupNames);
        }
    }

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
        if ($this->shouldUseDifferencialServing()) {
            $this->enqueueScriptsFn($groupNames, self::BUILD_TYPE_LEGACY);
            $this->enqueueScriptsFn($groupNames, self::BUILD_TYPE_MODERN);
        } else {
            $this->enqueueScriptsFn($groupNames);
        }
    }

    /** @inheritdoc */
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

        if ($buildType !== null) {
            $dependencyArray = array_map(function ($dependency) use ($buildType) {
                return $this->getGroupNameBy($dependency, $buildType);
            }, $dependencyArray);
        }

        return $dependencyArray;
    }

    private function getGroupNameBy(string $group, $buildType)
    {
        if ($buildType !== null) {
            return $group . '_wwp_' . $buildType;
        }

        return $group;
    }

    /** @inheritdoc */
    public function enqueueCritical(array $groupNames)
    {
        if ($this->shouldUseDifferencialServing()) {
            $this->enqueueCriticalFn($groupNames, self::BUILD_TYPE_LEGACY);
        } else {
            $this->enqueueCriticalFn($groupNames, self::BUILD_TYPE_MODERN);
        }
    }

    private function enqueueCriticalFn(array $groupNames, string $buildType = null)
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
     * @param string $distPath
     */
    private function checkIfShouldBeUsingDifferencialServing(string $distPath)
    {
        if (is_dir($this->getPathByBuildType($distPath, self::BUILD_TYPE_LEGACY))
            && is_dir($this->getPathByBuildType($distPath, self::BUILD_TYPE_MODERN))) {
            $this->initEntrypointsFileBy($distPath, self::BUILD_TYPE_LEGACY);
            $this->initEntrypointsFileBy($distPath, self::BUILD_TYPE_MODERN);
        }
    }

    /**
     * @param string $distPath
     * @param string $envType
     */
    private function initEntrypointsFileBy(string $distPath, string $envType)
    {
        $dir = $this->getPathByBuildType($distPath, $envType);
        $entrypointsFile = $dir . DIRECTORY_SEPARATOR . 'entrypoints.json';
        $versionFile = $dir . DIRECTORY_SEPARATOR . 'manifest.json';

        if (file_exists($entrypointsFile) && file_exists($versionFile)) {
            $this->entrypointsFiles[$envType] = json_decode(file_get_contents($entrypointsFile));
            $this->versionFiles[$envType] = json_decode(file_get_contents($versionFile));
        }
    }

    /**
     * @param string $distPath
     * @param string $type
     * @return string
     */
    private function getPathByBuildType(string $distPath, string $type)
    {
        return $distPath . DIRECTORY_SEPARATOR . $type;
    }

    /**
     * @param string $type
     * @param string $group
     * @param string|null $buildType
     * @return bool
     */
    private function isPropertyExistInManifest(string $type, string $group, $buildType)
    {
        if ($this->shouldUseDifferencialServing() && $buildType !== null) {
            $groupName = $type . '/' . $group;
            return property_exists($this->entrypointsFiles[$buildType]->entrypoints, $groupName);
        }

        return property_exists($this->manifest->{$type}, $group);
    }

    /**
     * @param string $type
     * @param string $group
     * @param string|null $buildType
     * @return string
     */
    private function getUrlSrcFrom(string $type, string $group, $buildType)
    {
        return $this->addBlogUrlTo($this->getSrcFrom($type, $group, $buildType));
    }

    /**
     * @param string $type
     * @param string $group
     * @param string|null $buildType
     * @return string
     */
    private function getPathSrcFrom(string $type, string $group, $buildType)
    {
        return $this->addDocumentRootTo($this->getSrcFrom($type, $group, $buildType));
    }

    /**
     * @param string $type
     * @param string $group
     * @param string|null $buildType
     * @return string
     */
    private function getSrcFrom(string $type, string $group, $buildType)
    {
        $asset = $this->manifest->site->assets_dest . '/' . $type . '/' . $group . $this->getVersion() . '.' . $type;

        if ($this->shouldUseDifferencialServing() && $buildType !== null) {
            $groupName = $type . '/' . $group;

            if (property_exists($this->entrypointsFiles[$buildType]->entrypoints, $groupName)
                && !empty($this->entrypointsFiles[$buildType]->entrypoints->{$groupName}->{$type})) {
                $assets = $this->entrypointsFiles[$buildType]->entrypoints->{$groupName}->{$type};

                // Note: take the last cause of webpack dependencies sorting
                return end($assets);
            }
        }

        return $asset;
    }

    /**
     * @return bool
     */
    private function shouldUseDifferencialServing()
    {
        return !empty($this->entrypointsFiles);
    }

    /**
     * @param string $path
     * @return string
     */
    private function addBlogUrlTo(string $path)
    {
        return $this->blogUrl . str_replace($this->container['wwp.asset.folder.prefix'], '', $path);
    }

    /**
     * @param string $path
     * @return string
     */
    private function addDocumentRootTo(string $path)
    {
        return $_SERVER['DOCUMENT_ROOT'] . str_replace($this->container['wwp.asset.folder.prefix'], '', $path);
    }

    /**
     * @param string|null $buildType
     * @return string
     */
    private function getVendorUrl($buildType)
    {
        $asset = str_replace($this->container['wwp.asset.folder.prefix'], '', $this->manifest->site->assets_dest . '/js/vendor' . $this->getVersion() . '.js');

        if ($buildType !== null && $this->shouldUseDifferencialServing()) {

            $asset = str_replace($this->container['wwp.asset.folder.prefix'], '', $this->manifest->site->assets_dest . '/' . $buildType . '/js/vendor.js');
            $asset = substr($asset, 1);

            if (property_exists($this->versionFiles[$buildType], $asset)) {
                return $this->addBlogUrlTo($this->versionFiles[$buildType]->{$asset});
            }
        }

        return $this->addBlogUrlTo(DIRECTORY_SEPARATOR . $asset);
    }
}
