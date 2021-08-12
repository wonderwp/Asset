<?php

namespace WonderWp\Component\Asset;

interface AssetEnqueuerInterface
{
    /**
     * @param array $groupNames
     */
    public function enqueueStyleGroups(array $groupNames);

    /**
     * @param array $groupNames
     */
    public function enqueueScriptGroups(array $groupNames);

    /**
     * @param array $groupNames
     */
    public function enqueueCriticalGroups(array $groupNames);

    /**
     * @param string $handle
     */
    public function enqueueStyle(string $handle);

    /**
     * @param string $handle
     */
    public function enqueueScript(string $handle);

    /**
     * @param string $handle
     */
    public function enqueueCritical(string $handle);
}
