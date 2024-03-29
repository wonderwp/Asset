<?php

namespace WonderWp\Component\Asset;

use WonderWp\Component\Asset\Exception\MissingJsonManifestException;

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
    /** @var WordpressAssetGateway */
    private $wordpressAssetGateway;
    /** @var \WP_Filesystem_Base */
    private $filesystem;

    public static $pathPattern = '{assets_dest}/{type}/{group}{version}.{type}';

    /**
     * @param AssetManager $assetManager
     * @param \WP_Filesystem_Base $filesystem
     * @param string $manifestPath
     * @param string $publicPath Path to asset location
     * @param string $blogUrl Website url
     * @param $version
     * @param WordpressAssetGateway|null $wordpressAssetGateway
     * @throws MissingJsonManifestException
     */
    public function __construct(
        AssetManager $assetManager,
        $filesystem,
        string $manifestPath,
        string $publicPath,
        string $blogUrl,
        $version,
        WordpressAssetGateway $wordpressAssetGateway = null
    )
    {
        parent::__construct($assetManager);

        if (is_null($wordpressAssetGateway)) {
            $this->wordpressAssetGateway = new WordpressAssetGateway();
        } else {
            $this->wordpressAssetGateway = $wordpressAssetGateway;
        }

        $this->filesystem = $filesystem;

        if (!$this->filesystem->exists($manifestPath)) {
            throw new MissingJsonManifestException(sprintf('File manifest does not exist : %s', $manifestPath));
        }

        $this->manifest = json_decode($this->filesystem->get_contents($manifestPath));

        $this->publicPath = $publicPath;

        $this->blogUrl = $this->wordpressAssetGateway->applyFilters('wwp.enqueuer.blogUrl', $blogUrl, $this);

        $this->version = $version;

        $this->register();
    }

    /**
     * Make js/css known to WordPress to be able to enqueue them more easily later on
     */
    private function register(): void
    {
        // Building up complete asset vision thanks to asset services
        $this->assetManager->callServices();

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

        $cssToRegister = $this->wordpressAssetGateway->applyFilters('wwp.enqueuer.register.cssToRegister', $cssToRegister, $this);

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

        $jsToRegister = $this->wordpressAssetGateway->applyFilters('wwp.enqueuer.register.jsToRegister', $jsToRegister, $this);

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
        $methodName = 'computeDependencyArray' . ucfirst($dependencyType);

        if (method_exists($this, $methodName)) {
            return call_user_func([$this, $methodName], $groupName);
        }

        return [];
    }

    /**
     * @param string $groupName
     * @return array
     */
    protected function computeDependencyArrayJs(string $groupName): array
    {
        return $this->manifest->jsDependencies->{$groupName} ?? [];
    }

    /**
     * @param string $groupName
     * @return array
     */
    protected function computeDependencyArrayCss(string $groupName): array
    {
        return $this->manifest->cssDependencies->{$groupName} ?? [];
    }

    /** @inheritdoc */
    public function enqueueStyleGroup(string $groupName)
    {
        $this->wordpressAssetGateway->enqueueStyle($groupName);

        return $this;
    }

    /** @inheritdoc */
    public function enqueueScriptGroup(string $groupName)
    {
        $this->wordpressAssetGateway->enqueueScript($groupName);

        return $this;
    }

    /** @inheritDoc */
    public function enqueueStyle(string $handle)
    {
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
        if ($this->doesPropertyExistInManifest('css', $groupName)) {
            $src = $this->getPathFrom('css', $groupName);

            if ($this->filesystem->exists($src)) {
                return $this->wordpressAssetGateway->applyFilters('wwp.enqueuer.inline.css.content', $this->filesystem->get_contents($src), $this);
            }
        }

        return '';
    }

    /** @inheritDoc */
    public function inlineScript(string $handle): string
    {
        $asset = $this->assetManager->getDependency('js', $handle);

        // This enqueur works with groups, not individual files,
        // hence retrieving the group from the file first, then inlining the group file.
        if ($asset) {
            return $this->inlineScriptGroup($asset->concatGroup);
        }

        return '';
    }

    /** @inheritDoc */
    public function inlineScriptGroup(string $groupName): string
    {
        if ($this->doesPropertyExistInManifest('js', $groupName)) {

            $src = $this->getPathFrom('js', $groupName);

            if ($this->filesystem->exists($src)) {
                return $this->wordpressAssetGateway->applyFilters('wwp.enqueuer.inline.js.content', $this->filesystem->get_contents($src), $this);
            }
        }

        return '';
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
        $search = [
            '{assets_dest}',
            '{type}',
            '{group}',
            '{version}'
        ];

        $replace = [
            $this->manifest->site->assets_dest,
            $type,
            $group,
            $this->version
        ];

        return str_replace($search, $replace, static::$pathPattern);
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
