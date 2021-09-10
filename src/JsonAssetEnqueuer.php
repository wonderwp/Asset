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
    protected $dest;
    protected $prefix;
    /**
     * @var WordpressAssetGateway
     */
    private $wordpressAssetGateway;

    public function __construct(AssetManager $assetManager, $manifestPath, $dest, $prefix, WordpressAssetGateway $wordpressAssetGateway = null)
    {
        parent::__construct($assetManager);
        $this->dest = $dest;
        $this->prefix = $prefix;
        $this->manifest = json_decode(file_get_contents($manifestPath));

        if ($wordpressAssetGateway === null) {
            $this->wordpressAssetGateway = new WordpressAssetGateway();
        } else {
            $this->wordpressAssetGateway = $wordpressAssetGateway;
        }

        $protocol = 'http';
        if (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {
            $protocol .= "s";
        }
        $protocol .= ':';
        $this->blogUrl = $this->wordpressAssetGateway->applyFilters('wwp.JsonAssetsEnqueur.blogUrl', $protocol . rtrim("//{$_SERVER['HTTP_HOST']}", '/'));


        $this->register();
    }

    private function register()
    {
        foreach ($this->manifest->css as $group => $styles) {
            $src = $this->getUrlSrcFrom('css', $group);

            if (!empty($src)) {
                $this->wordpressAssetGateway->registerStyle($group, $src, [], null);
            }
        }

        $src = $this->getVendorUrl();
        $dependencies = $this->computeDependencyArray('vendor');
        if (!empty($src)) {
            $this->wordpressAssetGateway->registerScript('vendor', $src, $dependencies, null, true);
        }

        foreach ($this->manifest->js as $group => $scripts) {
            $dependencies = $this->computeDependencyArray($group);

            $src = $this->getUrlSrcFrom('js', $group);

            if (!empty($src)) {
                $this->wordpressAssetGateway->registerScript($group, $src, $dependencies, null, true);
            }
        }
    }

    /**
     * @param string $groupName
     * @return string[]
     */
    protected function computeDependencyArray(string $groupName)
    {
        $dependencyArray = isset($this->manifest->jsDependencies->{$groupName}) ? $this->manifest->jsDependencies->{$groupName} : [];

        return $dependencyArray;
    }

    /** @inheritdoc */
    public function enqueueStyleGroup(string $groupName)
    {
        $this->enqueueStyle($groupName);
    }

    /** @inheritdoc */
    public function enqueueScriptGroup(string $groupName)
    {
        $this->enqueueScript($groupName);
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
        if ($this->isPropertyExistInManifest('css', $handle)) {
            $src = $this->getPathSrcFrom('css', $handle);

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
        if ($this->isPropertyExistInManifest('js', $handle)) {
            $src = $this->getPathSrcFrom('js', $handle);

            if (file_exists($src)) {
                return $this->wordpressAssetGateway->applyFilters('wwp.enqueur.critical.js.content', file_get_contents($src));
            }
        }

        return '';
    }

    /** @inheritDoc */
    public function inlineScriptGroup(string $groupName)
    {
        $this->inlineScript($groupName);
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

        return $this->addBlogUrlTo($asset);
    }
}
