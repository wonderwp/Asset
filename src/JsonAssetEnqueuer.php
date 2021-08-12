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
    protected $dest;
    protected $prefix;

    public function __construct(AssetManager $assetManager, $manifestPath, $dest, $prefix)
    {
        parent::__construct($assetManager);
        $this->dest = $dest;
        $this->prefix = $prefix;
        $this->manifest = json_decode(file_get_contents($manifestPath));

        $protocol = 'http';
        if (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {
            $protocol .= "s";
        }
        $protocol .= ':';
        $this->blogUrl = apply_filters('wwp.JsonAssetsEnqueur.blogUrl', $protocol . rtrim("//{$_SERVER['HTTP_HOST']}", '/'));
    }

    /** @inheritdoc */
    public function enqueueStyleGroups(array $groupNames)
    {
        $this->enqueueStylesFn($groupNames);
    }

    /**
     * @param array $groupNames
     */
    public function enqueueStylesFn(array $groupNames)
    {
        foreach ($groupNames as $group) {
            if ($this->isPropertyExistInManifest('css', $group)) {
                $src = $this->getUrlSrcFrom('css', $group);

                $groupName = $this->getGroupNameBy($group);

                wp_enqueue_style($groupName, $src, [], null);
            }
        }
    }

    /** @inheritdoc */
    public function enqueueScriptGroups(array $groupNames)
    {
        $this->enqueueScriptsFn($groupNames);
    }

    /**
     * @param array $groupNames
     */
    public function enqueueScriptsFn(array $groupNames)
    {
        $availableGroups = !empty($this->manifest->js) ? array_keys(get_object_vars($this->manifest->js)) : [];

        foreach ($groupNames as $group) {
            // Note: we couldn't check for isPropertyExistInManifest
            // because js vendor is not present but should be loaded
            $dependencies = $this->computeDependencyArray($group, $availableGroups);

            if ($group === 'vendor') {
                $src = $this->getVendorUrl();
            } else {
                $src = $this->getUrlSrcFrom('js', $group);
            }

            $groupName = $this->getGroupNameBy($group);

            if (!empty($src)) {
                wp_enqueue_script($groupName, $src, $dependencies, null, true);
            }
        }
    }

    protected function computeDependencyArray($groupName, $availableGroups)
    {
        $dependencyArray = isset($this->manifest->jsDependencies->{$groupName}) ? $this->manifest->jsDependencies->{$groupName} : [];

        return $dependencyArray;
    }

    protected function getGroupNameBy(string $group)
    {
        return $group;
    }

    /** @inheritdoc */
    public function enqueueCriticalGroups(array $handle)
    {
        $this->enqueueCriticalFn($handle);
    }

    protected function enqueueCriticalFn(array $groupNames)
    {
        foreach ($groupNames as $group) {
            if ($this->isPropertyExistInManifest('js', $group)) {
                $src = $this->getPathSrcFrom('js', $group);

                if (file_exists($src)) {
                    $content = apply_filters('wwp.enqueur.critical.js.content', file_get_contents($src));
                    if (!empty($content)) {
                        echo '<script id="critical-js">
                            ' . $content . '
                            </script>';
                    }
                }
            }

            if ($this->isPropertyExistInManifest('css', $group)) {
                $src = $this->getPathSrcFrom('css', $group);

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
            $fileVersion = $_SERVER['DOCUMENT_ROOT'] . $this->dest . '/version.php';
            $this->version = file_exists($fileVersion) ? include($fileVersion) : null;
        }

        return $this->version;
    }

    /**
     * @param string $type
     * @param string $group
     * @return bool
     */
    protected function isPropertyExistInManifest(string $type, string $group)
    {
        return property_exists($this->manifest->{$type}, $group);
    }

    /**
     * @param string $type
     * @param string $group
     * @return string
     */
    protected function getUrlSrcFrom(string $type, string $group)
    {
        return $this->addBlogUrlTo($this->getSrcFrom($type, $group));
    }

    /**
     * @param string $type
     * @param string $group
     * @return string
     */
    protected function getPathSrcFrom(string $type, string $group)
    {
        return $this->addDocumentRootTo($this->getSrcFrom($type, $group));
    }

    /**
     * @param string $type
     * @param string $group
     * @return string
     */
    protected function getSrcFrom(string $type, string $group)
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
        return $this->blogUrl . str_replace($this->prefix, '', $path);
    }

    /**
     * @param string $path
     * @return string
     */
    protected function addDocumentRootTo(string $path)
    {
        return $_SERVER['DOCUMENT_ROOT'] . str_replace($this->prefix, '', $path);
    }

    /**
     * @return string
     */
    protected function getVendorUrl()
    {
        $asset = str_replace($this->prefix, '', $this->manifest->site->assets_dest . '/js/vendor' . $this->getVersion() . '.js');

        return $this->addBlogUrlTo(DIRECTORY_SEPARATOR . $asset);
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
