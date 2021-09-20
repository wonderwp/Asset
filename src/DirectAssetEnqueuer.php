<?php

namespace WonderWp\Component\Asset;

class DirectAssetEnqueuer extends AbstractAssetEnqueuer
{
    private $publicPath;
    /**
     * @var WordpressAssetGateway
     */
    private $wordpressAssetGateway;

    /**
     * @param AssetManager $assetManager
     * @param string $publicPath Path to asset location
     * @param WordpressAssetGateway|null $wordpressAssetGateway
     * @param null $filesystem
     */
    public function __construct(AssetManager $assetManager, $filesystem, string $publicPath, WordpressAssetGateway $wordpressAssetGateway = null)
    {
        parent::__construct($assetManager, $filesystem);

        $this->assetManager->callServices();
        $this->publicPath = $publicPath;
        if ($wordpressAssetGateway === null) {
            $this->wordpressAssetGateway = new WordpressAssetGateway();
        } else {
            $this->wordpressAssetGateway = $wordpressAssetGateway;
        }

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

        foreach ($this->assetManager->getDependencies('css') as $dep) {
            /** @var Asset $dep */
            $cssToRegister[$dep->handle] = [
                'handle' => $dep->handle,
                'src' => $dep->src,
                'deps' => $dep->deps,
                'ver' => $dep->ver,
            ];
        }

        $cssToRegister = $this->wordpressAssetGateway->applyFilters('wwp.enqueuer.register.cssAssets', $cssToRegister, $this);

        foreach ($cssToRegister as $cssAsset) {
            $this->wordpressAssetGateway->registerStyle($cssAsset['handle'], $cssAsset['src'], $cssAsset['deps'], $cssAsset['ver'], $cssAsset['media'] ?? null);
        }
    }

    private function registerScripts(): void
    {
        $jsToRegister = [];

        foreach ($this->assetManager->getDependencies('js') as $dep) {
            /** @var Asset $dep */
            $jsToRegister[$dep->handle] = [
                'handle' => $dep->handle,
                'src' => $dep->src,
                'deps' => $dep->deps,
                'ver' => $dep->ver,
            ];
        }

        $jsToRegister = $this->wordpressAssetGateway->applyFilters('wwp.enqueuer.register.jsAssets', $jsToRegister, $this);

        foreach ($jsToRegister as $jsAsset) {
            $this->wordpressAssetGateway->registerScript($jsAsset['handle'], $jsAsset['src'], $jsAsset['deps'], $jsAsset['ver'], $jsAsset['in_footer'] ?? null);
        }
    }

    /** @inheritdoc */
    public function enqueueStyleGroup(string $groupName)
    {
        $toEnqueue = $this->assetManager->getDependenciesFromGroup($groupName, 'css');

        foreach ($toEnqueue as $dep) {
            $this->enqueueStyle($dep->handle);
        }

        return $this;
    }

    /** @inheritdoc */
    public function enqueueScriptGroup(string $groupName)
    {
        $toEnqueue = $this->assetManager->getDependenciesFromGroup($groupName, 'js');

        foreach ($toEnqueue as $dep) {
            $this->enqueueScript($dep->handle);
        }

        return $this;
    }

    /** @inheritdoc */
    public function enqueueStyle(string $handle)
    {
        $this->wordpressAssetGateway->enqueueStyle($handle);

        return $this;
    }

    /** @inheritdoc */
    public function enqueueScript(string $handle)
    {
        $this->wordpressAssetGateway->enqueueScript($handle);

        return $this;
    }

    /** @inheritdoc */
    public function inlineStyle(string $handle): string
    {
        return $this->inline($handle, 'css');
    }

    /** @inheritdoc */
    public function inlineScript(string $handle): string
    {
        return $this->inline($handle, 'js');
    }

    /** @inheritdoc */
    public function inlineStyleGroup(string $groupName): string
    {
        $toInline = $this->assetManager->getDependenciesFromGroup($groupName, 'css');
        $inline = '';

        foreach ($toInline as $dep) {
            $inline .= $this->inlineStyle($dep->handle);
        }

        return $inline;
    }

    /** @inheritdoc */
    public function inlineScriptGroup(string $groupName): string
    {
        $toInline = $this->assetManager->getDependenciesFromGroup($groupName, 'js');
        $inline = '';

        foreach ($toInline as $dep) {
            $inline .= $this->inlineScript($dep->handle);
        }

        return $inline;
    }

    /**
     * @param string $handle
     * @param string $dependencyType
     * @return false|string
     */
    private function inline(string $handle, string $dependencyType)
    {
        $dep = $this->assetManager->getDependency($dependencyType, $handle);

        if ($dep) {
            return $this->wordpressAssetGateway->applyFilters('wwp.enqueuer.inline.' . $dependencyType . '.content', $this->getAssetContent($dep), $this);
        }

        return '';
    }

    /**
     * @param Asset $dep
     * @return false|string
     */
    private function getAssetContent(Asset $dep)
    {
        if ($this->isAbsoluteUrl($dep->src)) {
            return file_get_contents($dep->src);
        }

        return $this->filesystem->get_contents($this->publicPath . DIRECTORY_SEPARATOR . $dep->src);
    }

    /**
     * @param string $url
     * @return bool
     */
    protected function isAbsoluteUrl(string $url)
    {
        return str_contains($url, '://') || '//' === substr($url, 0, 2);
    }
}
