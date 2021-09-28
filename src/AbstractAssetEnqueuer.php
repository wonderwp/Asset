<?php

namespace WonderWp\Component\Asset;

abstract class AbstractAssetEnqueuer implements AssetEnqueuerInterface
{
    /** @var AssetManager */
    protected $assetManager;

    /** Constructor */
    public function __construct(AssetManager $assetManager)
    {
        $this->assetManager = $assetManager;
    }

    /** @inheritdoc */
    public function enqueueScriptGroups(array $groupNames)
    {
        foreach ($groupNames as $groupName) {
            $this->enqueueScriptGroup($groupName);
        }

        return $this;
    }

    /** @inheritdoc */
    public function enqueueStyleGroups(array $groupNames)
    {
        foreach ($groupNames as $groupName) {
            $this->enqueueStyleGroup($groupName);
        }

        return $this;
    }

    /** @inheritdoc */
    public function enqueueStyles(array $handles)
    {
        foreach ($handles as $handle) {
            $this->enqueueStyle($handle);
        }

        return $this;
    }

    /** @inheritdoc */
    public function enqueueScripts(array $handles)
    {
        foreach ($handles as $handle) {
            $this->enqueueScript($handle);
        }

        return $this;
    }

    /** @inheritdoc */
    public function inlineStyles(array $handles)
    {
        $styles = '';

        foreach ($handles as $handle) {
            $styles .= $this->inlineStyle($handle);
        }

        return $styles;
    }

    /** @inheritdoc */
    public function inlineStyleGroups(array $groupNames)
    {
        $styles = '';

        foreach ($groupNames as $groupName) {
            $styles .= $this->inlineStyleGroup($groupName);
        }

        return $styles;
    }

    /** @inheritdoc */
    public function inlineScripts(array $handles)
    {
        $scripts = '';
        foreach ($handles as $handle) {
            $scripts .= $this->inlineScript($handle);
        }

        return $scripts;
    }

    /** @inheritdoc */
    public function inlineScriptGroups(array $groupNames)
    {
        $scripts = '';

        foreach ($groupNames as $groupName) {
            $scripts .= $this->inlineScriptGroup($groupName);
        }

        return $scripts;
    }

    /**
     * @param AssetManager $assetManager
     */
    public function setAssetManager(AssetManager $assetManager): void
    {
        $this->assetManager = $assetManager;
    }
}
