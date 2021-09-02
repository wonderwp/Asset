<?php

namespace WonderWp\Component\Asset;

class DirectAssetEnqueuer extends AbstractAssetEnqueuer
{
    private $publicPath;
    /**
     * @var WordpressAssetGateway
     */
    private $wordpressAssetGateway;

    /** @inheritdoc */
    public function __construct(AssetManager $assetManager, $publicPath, WordpressAssetGateway $wordpressAssetGateway = null)
    {
        parent::__construct($assetManager);

        $this->assetManager->callServices();
        $this->publicPath = $publicPath;
        if ($wordpressAssetGateway === null) {
            $this->wordpressAssetGateway = new WordpressAssetGateway();
        } else {
            $this->wordpressAssetGateway = $wordpressAssetGateway;
        }
        $this->register();
    }

    private function register()
    {
        $toRender = $this->assetManager->getDependencies('css');

        foreach ($toRender as $dep) {
            /* @var $dep Asset */
            $this->wordpressAssetGateway->registerStyle($dep->handle, $dep->src, $dep->deps, $dep->ver);
        }


        $toRender = $this->assetManager->getDependencies('js');

        foreach ($toRender as $dep) {
            /* @var $dep Asset */
            $this->wordpressAssetGateway->registerScript($dep->handle, $dep->src, $dep->deps, $dep->ver);
        }
    }

    /** @inheritdoc */
    public function enqueueStyleGroups(array $groupNames)
    {
        $toRender = $this->assetManager->getDependencies('css');

        foreach ($toRender as $dep) {
            /* @var $dep Asset */
            if (in_array($dep->concatGroup, $groupNames)) {
                $this->enqueueStyle($dep->handle);
            }
        }
    }

    /** @inheritdoc */
    public function enqueueScriptGroups(array $groupNames)
    {
        $toRender = $this->assetManager->getDependencies('js');

        foreach ($toRender as $dep) {
            /* @var $dep Asset */
            if (in_array($dep->concatGroup, $groupNames)) {
                $this->enqueueScript($dep->handle);
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
        return $this->inline($handle, 'css');
    }

    public function inlineScript(string $handle): string
    {
        return $this->inline($handle, 'js');
    }

    /**
     * @param string $handle
     * @param string $dependencyType
     * @return false|string
     */
    private function inline(string $handle, string $dependencyType)
    {
        $toRender = $this->assetManager->getDependencies($dependencyType);

        foreach ($toRender as $dep) {
            /* @var $dep Asset */
            if ($handle === $dep->handle) {
                return $this->getAssetContent($dep);
            }
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
        } else {
            return file_get_contents($this->publicPath . DIRECTORY_SEPARATOR . $dep->src);
        }
    }

    protected function isAbsoluteUrl(string $url)
    {
        return str_contains($url, '://') || '//' === substr($url, 0, 2);
    }
}
