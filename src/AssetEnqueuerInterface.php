<?php

namespace WonderWp\Component\Asset;

interface AssetEnqueuerInterface
{
    /**
     * Enqueue styles matching given concat groups
     *
     * @param string[] $groupNames
     * @return self
     */
    public function enqueueStyleGroups(array $groupNames);

    /**
     * Enqueue styles according to one asset concat group
     * @return self
     */
    public function enqueueStyleGroup(string $groupName);

    /**
     * Enqueue scripts matching given concat groups
     *
     * @param string[] $groupNames
     * @return self
     */
    public function enqueueScriptGroups(array $groupNames);

    /**
     * Enqueue scripts according to one asset concat group
     * @return self
     */
    public function enqueueScriptGroup(string $groupName);

    /**
     * Enqueue several styles based on their handle
     *
     * @param string[] $handles
     * @return self
     */
    public function enqueueStyles(array $handles);

    /**
     * Enqueue one style based on its handle
     *
     * @return self
     */
    public function enqueueStyle(string $handle);

    /**
     * Enqueue several scripts based on their handle
     *
     * @param string[] $handles
     * @return self
     */
    public function enqueueScripts(array $handles);

    /**
     * Enqueue one script based on its handle
     *
     * @return self
     */
    public function enqueueScript(string $handle);

    /**
     * Return several stylesheet files content into a string based on their handle
     *
     * @param string[] $handles
     * @return string
     */
    public function inlineStyles(array $handles);

    /**
     * Return several stylesheet files content into a string based on the given concat group
     *
     * @return string
     */
    public function inlineStyleGroup(string $groupName);

    /**
     * Return several stylesheet files content into a string based on their concat groups
     *
     * @return string
     */
    public function inlineStyleGroups(array $groupNames);

    /**
     * Return one stylesheet file content into a string based on its handle
     *
     * @return string
     */
    public function inlineStyle(string $handle);

    /**
     * Return several script files content into a string based on their handle
     *
     * @param string[] $handles
     * @return string
     */
    public function inlineScripts(array $handles);

    /**
     * Return several script files content into a string based on the given concat group
     *
     * @return string
     */
    public function inlineScriptGroup(string $groupName);

    /**
     * Return several script files content into a string based on their concat groups
     *
     * @return string
     */
    public function inlineScriptGroups(array $groupNames);

    /**
     * Return one script file content into a string based on its handle
     *
     * @return string
     */
    public function inlineScript(string $handle);
}
