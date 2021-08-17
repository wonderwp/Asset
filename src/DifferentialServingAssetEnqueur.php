<?php


namespace WonderWp\Component\Asset;


class DifferentialServingAssetEnqueur extends JsonAssetEnqueuer
{
    const BUILD_TYPE_LEGACY = 'legacy';
    const BUILD_TYPE_MODERN = 'modern';

    /** @var object[] */
    protected $entrypointsFiles = [];
    /** @var object[] */
    protected $versionFiles = [];

    /**
     * @inerhitDoc
     */
    public function __construct(string $manifestPath)
    {
        parent::__construct($manifestPath);

        $this->initDifferentialServing($this->getDistPath());
    }

    /**
     * @return string
     */
    private function getDistPath()
    {
        return $_SERVER['DOCUMENT_ROOT'] . $this->container['wwp.asset.folder.dest'];
    }

    /** @inheritdoc */
    public function enqueueStyles(array $groupNames)
    {
        return $this->enqueueStylesFn($groupNames, self::BUILD_TYPE_LEGACY);
    }

    /**
     * @inerhitDoc
     */
    public function enqueueScripts(array $groupNames)
    {
        $this->enqueueScriptsFn($groupNames, self::BUILD_TYPE_LEGACY);
        $this->enqueueScriptsFn($groupNames, self::BUILD_TYPE_MODERN);
    }

    /** @inheritdoc */
    public function enqueueCritical(array $groupNames)
    {
        $this->enqueueCriticalFn($groupNames, self::BUILD_TYPE_LEGACY);
    }

    /**
     * @param string $distPath
     */
    private function initDifferentialServing(string $distPath)
    {
        $this->initEntrypointsFileBy($distPath, self::BUILD_TYPE_LEGACY);
        $this->initEntrypointsFileBy($distPath, self::BUILD_TYPE_MODERN);
    }

    /**
     * @param string $distPath
     * @param string $envType
     */
    private function initEntrypointsFileBy(string $distPath, string $envType)
    {
        $dir = $this->getPathByBuildType($distPath, $envType);
        $entrypointsFile = $dir . DIRECTORY_SEPARATOR . 'entrypoints.json';
        $versionFile = $dir . DIRECTORY_SEPARATOR . 'manifest.json';

        if (file_exists($entrypointsFile) && file_exists($versionFile)) {
            $this->entrypointsFiles[$envType] = json_decode(file_get_contents($entrypointsFile));
            $this->versionFiles[$envType] = json_decode(file_get_contents($versionFile));
        }
    }

    protected function computeDependencyArray($groupName, $availableGroups, $buildType)
    {
        $dependencyArray = parent::computeDependencyArray($groupName, $availableGroups, $buildType);

        return array_map(function ($dependency) use ($buildType) {
            return $this->getGroupNameBy($dependency, $buildType);
        }, $dependencyArray);
    }

    /**
     * @inerhitDoc
     */
    protected function getGroupNameBy(string $group, $buildType)
    {
        return $group . '_wwp_' . $buildType;
    }

    /**
     * @param string $distPath
     * @param string $type
     * @return string
     */
    private function getPathByBuildType(string $distPath, string $type)
    {
        return $distPath . DIRECTORY_SEPARATOR . $type;
    }

    /**
     * @param string $type
     * @param string $group
     * @param string|null $buildType
     * @return bool
     */
    protected function isPropertyExistInManifest(string $type, string $group, $buildType)
    {
        $groupName = $type . '/' . $group;
        return property_exists($this->entrypointsFiles[$buildType]->entrypoints, $groupName);
    }

    /**
     * @param string $type
     * @param string $group
     * @param string|null $buildType
     * @return string
     */
    protected function getSrcFrom(string $type, string $group, $buildType)
    {
        $groupName = $type . '/' . $group;

        if (property_exists($this->entrypointsFiles[$buildType]->entrypoints, $groupName)
            && !empty($this->entrypointsFiles[$buildType]->entrypoints->{$groupName}->{$type})) {
            $assets = $this->entrypointsFiles[$buildType]->entrypoints->{$groupName}->{$type};

            // Note: take the last cause of webpack dependencies sorting
            return end($assets);
        }
    }

    /**
     * @param string|null $buildType
     * @return string
     */
    protected function getVendorUrl($buildType)
    {
        $asset = str_replace($this->container['wwp.asset.folder.prefix'], '', $this->manifest->site->assets_dest . '/' . $buildType . '/js/vendor.js');
        $asset = substr($asset, 1);

        if (property_exists($this->versionFiles[$buildType], $asset)) {
            return $this->addBlogUrlTo($this->versionFiles[$buildType]->{$asset});
        }
    }
}
