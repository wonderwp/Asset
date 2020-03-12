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
    public function __construct($manifestPath)
    {
        parent::__construct();

        $this->manifest = json_decode(file_get_contents($manifestPath));
        $this->blogUrl  = rtrim("//{$_SERVER['HTTP_HOST']}", '/');
    }

    /** @inheritdoc */
    public function enqueueStyles(array $groupNames)
    {
        $versionNum = $this->getVersion();
        foreach ($groupNames as $group) {
            if (array_key_exists($group, $this->manifest->css)) {
                $src = $this->blogUrl . str_replace($this->container['wwp.asset.folder.prefix'], '', $this->manifest->site->assets_dest) . '/css/' . $group . $versionNum . '.css';
                wp_enqueue_style($group, $src, [], null);
            }
        }
    }

    /** @inheritdoc */
    public function enqueueScripts(array $groupNames)
    {
        $versionNum      = $this->getVersion();
        $availableGroups = array_keys(get_object_vars($this->manifest->js));

        foreach ($groupNames as $group) {
            $dependencies = $this->computeDependencyArray($group, $availableGroups);
            $src          = $this->blogUrl . str_replace($this->container['wwp.asset.folder.prefix'], '', $this->manifest->site->assets_dest) . '/js/' . $group . $versionNum . '.js';
            wp_enqueue_script($group, $src, $dependencies, null, true);
        }
    }

    protected function computeDependencyArray($groupName, $availableGroups)
    {
        $dependencyArray = isset($this->manifest->jsDependencies->{$groupName}) ? $this->manifest->jsDependencies->{$groupName} : [];

        return $dependencyArray;
    }

    /** @inheritdoc */
    public function enqueueCritical(array $groupNames)
    {
        $versionNum = $this->getVersion();
        foreach ($groupNames as $group) {
            if (array_key_exists($group, $this->manifest->js)) {
                $src = $_SERVER['DOCUMENT_ROOT'] . str_replace($this->container['wwp.asset.folder.prefix'], '', $this->manifest->site->assets_dest) . '/js/' . $group . $versionNum . '.js';
                if (file_exists($src)) {
                    $content = apply_filters('wwp.enqueur.critical.js.content', file_get_contents($src));
                    if (!empty($content)) {
                        echo '<script id="critical-js">
                                ' . $content . '
                            </script>';
                    }
                }
            }

            if (array_key_exists($group, $this->manifest->css)) {
                $src = $_SERVER['DOCUMENT_ROOT'] . str_replace($this->container['wwp.asset.folder.prefix'], '', $this->manifest->site->assets_dest) . '/css/' . $group . $versionNum . '.css';
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
            $fileVersion   = $_SERVER['DOCUMENT_ROOT'] . $this->container['wwp.asset.folder.dest'] . '/version.php';
            $this->version = file_exists($fileVersion) ? include($fileVersion) : null;
        }

        return $this->version;
    }
}
