<?php

namespace WonderWp\Component\Asset;

class DirectAssetEnqueuer extends AbstractAssetEnqueuer
{
    private $publicPath;

    /** @inheritdoc */
    public function __construct(AssetManager $assetManager, $publicPath)
    {

        parent::__construct($assetManager);

        $this->assetManager->callServices();
        $this->publicPath = $publicPath;
        $this->register();
    }

    private function register()
    {
        $toRender = $this->assetManager->getDependencies('css');

        foreach ($toRender as $dep) {
            /* @var $dep Asset */
            wp_register_style($dep->handle, $dep->src, $dep->deps, $dep->ver);
        }


        $toRender = $this->assetManager->getDependencies('js');

        foreach ($toRender as $dep) {
            /* @var $dep Asset */
            wp_register_script($dep->handle, $dep->src, $dep->deps, $dep->ver);
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
        wp_enqueue_style($handle);
    }

    public function enqueueScript(string $handle)
    {
        wp_enqueue_script($handle);
    }

    public function inlineStyle(string $handle)
    {
        $toRender = $this->assetManager->getDependencies('css');

        foreach ($toRender as $dep) {
            /* @var $dep Asset */
            if ($handle === $dep->handle) {
                return $this->getAssetContent($dep);
            }
        }

        return '';
    }

    public function inlineScript(string $handle): string
    {
        $toRender = $this->assetManager->getDependencies('js');

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
