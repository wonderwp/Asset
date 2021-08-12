<?php

namespace WonderWp\Component\Asset;

class DirectAssetEnqueuer extends AbstractAssetEnqueuer
{
    /** @var AssetManager */
    protected $assetsManager;

    /** @inheritdoc */
    public function __construct(AssetManager $assetsManager)
    {
        parent::__construct($assetsManager);

        $this->assetsManager->callServices();
    }

    /** @inheritdoc */
    public function enqueueStyleGroups(array $groupNames)
    {
        $toRender = $this->assetsManager->getDependencies('css');

        foreach ($toRender as $dep) {
            /* @var $dep Asset */
            if (in_array($dep->concatGroup, $groupNames)) {
                wp_enqueue_style($dep->handle, $dep->src, $dep->deps, $dep->ver);
            }
        }
    }

    /** @inheritdoc */
    public function enqueueScriptGroups(array $groupNames) { }

    /** @inheritdoc */
    public function enqueueCriticalGroups(array $groupNames) { }

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
