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
    /** @var string */
    private $publicPath;
    /**
     * @var WordpressAssetGateway
     */
    private $wordpressAssetGateway;

    /**
     * @param AssetManager $assetManager
     * @param string $manifestPath
     * @param string $publicPath Path to asset location
     * @param string $blogUrl Website url
     * @param WordpressAssetGateway|null $wordpressAssetGateway
     */
    public function __construct(AssetManager $assetManager, $filesystem, string $manifestPath, string $publicPath, string $blogUrl, WordpressAssetGateway $wordpressAssetGateway = null)
    {
        parent::__construct($assetManager, $filesystem);

        if ($wordpressAssetGateway === null) {
            $this->wordpressAssetGateway = new WordpressAssetGateway();
        } else {
            $this->wordpressAssetGateway = $wordpressAssetGateway;
        }

        $this->manifest = json_decode($this->filesystem->get_contents($manifestPath));

        $this->publicPath = $publicPath;

        $this->blogUrl = $this->wordpressAssetGateway->applyFilters('wwp.enqueuer.blogUrl', $blogUrl, $this);

        $this->register();
    }

    /**
     * Make js/css known to WordPress to be able to enqueue them more easily later on
     */
    private function register(): void
    {
        $this->registerStyles();
        $this->registerScripts();
    }

    private function registerStyles(): void
    {
        $cssToRegister = [];

        $manifestCssAssets = $this->wordpressAssetGateway->applyFilters('wwp.enqueuer.register.cssAssets', (array) $this->manifest->css, $this);

        foreach ($manifestCssAssets as $group => $styles) {
            $dependencies = $this->computeDependencyArray($group, 'css');

            $src = $this->getUrlFrom('css', $group);

            if (!empty($src)) {
                $cssToRegister[$group] = [
                    'handle' => $group,
                    'src' => $src,
                    'deps' => $dependencies,
                    'ver' => null,
                    'media' => null
                ];
            }
        }

        foreach ($cssToRegister as $cssAsset) {
            $this->wordpressAssetGateway->registerStyle($cssAsset['handle'], $cssAsset['src'], $cssAsset['deps'], $cssAsset['ver'], $cssAsset['media']);
        }
    }

    private function registerScripts(): void
    {
        $jsToRegister = [];

        $manifestJsAssets = $this->wordpressAssetGateway->applyFilters('wwp.enqueuer.register.jsAssets', (array) $this->manifest->js, $this);

        foreach ($manifestJsAssets as $group => $scripts) {
            $dependencies = $this->computeDependencyArray($group, 'js');

            $src = $this->getUrlFrom('js', $group);

            if (!empty($src)) {
                $jsToRegister[$group] = [
                    'handle' => $group,
                    'src' => $src,
                    'deps' => $dependencies,
                    'ver' => null,
                    'in_footer' => true
                ];
            }
        }

        foreach ($jsToRegister as $jsAsset) {
            $this->wordpressAssetGateway->registerScript($jsAsset['handle'], $jsAsset['src'], $jsAsset['deps'], $jsAsset['ver'], $jsAsset['in_footer']);
        }
    }

    /**
     * @param string $groupName
     * @param string $dependencyType
     * @return string[]
     */
    protected function computeDependencyArray(string $groupName, string $dependencyType): array
    {
        switch ($dependencyType) {
            case 'js':
                return $this->manifest->jsDependencies->{$groupName} ?? [];
            case 'css':
                return $this->manifest->cssDependencies->{$groupName} ?? [];
            default:
                return [];
        }
    }

    /** @inheritdoc */
    public function enqueueStyleGroup(string $groupName)
    {
        $this->enqueueStyle($groupName);

        return $this;
    }

    /** @inheritdoc */
    public function enqueueScriptGroup(string $groupName)
    {
        $this->enqueueScript($groupName);

        return $this;
    }

    /** @inheritDoc */
    public function enqueueStyle(string $handle)
    {
        $this->wordpressAssetGateway->enqueueStyle($handle);

        return $this;
    }

    /** @inheritDoc */
    public function enqueueScript(string $handle)
    {
        $this->wordpressAssetGateway->enqueueScript($handle);

        return $this;
    }

    /** @inheritDoc */
    public function inlineStyle(string $handle)
    {
        if ($this->doesPropertyExistInManifest('css', $handle)) {
            $src = $this->getPathFrom('css', $handle);

            if ($this->filesystem->exists($src)) {
                return $this->wordpressAssetGateway->applyFilters('wwp.enqueuer.inline.css.content', $this->filesystem->get_contents($src), $this);
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
        if ($this->doesPropertyExistInManifest('js', $handle)) {

            $src = $this->getPathFrom('js', $handle);

            if ($this->filesystem->exists($src)) {
                return $this->wordpressAssetGateway->applyFilters('wwp.enqueuer.inline.js.content', $this->filesystem->get_contents($src), $this);
            }
        }

        return '';
    }

    /** @inheritDoc */
    public function inlineScriptGroup(string $groupName): string
    {
        return $this->inlineScript($groupName);
    }

    /**
     * @return int|null
     */
    public function getVersion(): ?int
    {
        if ($this->version === null) {
            $fileVersion = $this->publicPath . $this->manifest->site->assets_dest . '/version.php';
            $this->version = $this->filesystem->exists($fileVersion) ? include($fileVersion) : null;
        }

        return $this->version;
    }

    /**
     * @param string $type
     * @param string $group
     * @return bool
     */
    protected function doesPropertyExistInManifest(string $type, string $group): bool
    {
        return property_exists($this->manifest->{$type}, $group);
    }

    /**
     * @param string $type
     * @param string $group
     * @return string
     */
    protected function getUrlFrom(string $type, string $group): string
    {
        return $this->addBlogUrlTo($this->getSrcFrom($type, $group));
    }

    /**
     * @param string $type
     * @param string $group
     * @return string
     */
    protected function getPathFrom(string $type, string $group): string
    {
        return $this->addDocumentRootTo($this->getSrcFrom($type, $group));
    }

    /**
     * @param string $type
     * @param string $group
     * @return string
     */
    protected function getSrcFrom(string $type, string $group): string
    {
        return $this->manifest->site->assets_dest . '/' . $type . '/' . $group . $this->getVersion() . '.' . $type;
    }

    /**
     * @param string $path
     * @return string
     */
    protected function addBlogUrlTo(string $path): string
    {
        return $this->blogUrl . $path;
    }

    /**
     * @param string $path
     * @return string
     */
    protected function addDocumentRootTo(string $path): string
    {
        return $this->publicPath . str_replace($this->manifest->site->prefix, '', $path);
    }
}
