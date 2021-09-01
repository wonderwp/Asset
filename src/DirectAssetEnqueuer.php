<?php

namespace WonderWp\Component\Asset;

class DirectAssetEnqueuer extends AbstractAssetEnqueuer
{

    /** @inheritdoc */
    public function __construct(AssetManager $assetManager)
    {

        parent::__construct($assetManager);

        $this->assetManager->callServices();
        $this->register();
    }

    /** @inheritdoc */
    public function enqueueStyleGroups(array $groupNames)
    {
        $toRender = $this->assetManager->getDependencies('css');

        foreach ($toRender as $dep) {
            /* @var $dep Asset */
            if (in_array($dep->concatGroup, $groupNames)) {
                wp_enqueue_style($dep->handle);
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
                wp_enqueue_script($dep->handle);
            }
        }
    }

    /** @inheritdoc */
    public function enqueueCriticalGroups(array $groupNames)
    {

    }

    public function enqueueStyle(string $handle)
    {
        wp_enqueue_style($handle);
    }

    public function enqueueScript(string $handle)
    {
        wp_enqueue_script($handle);
    }

    public function enqueueCritical(string $handle)
    {

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
}
