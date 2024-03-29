<?php

namespace WonderWp\Component\Asset;

use WonderWp\Component\DependencyInjection\SingletonInterface;
use WonderWp\Component\DependencyInjection\SingletonTrait;
use function WonderWp\Functions\array_merge_recursive_distinct;

class AssetManager implements SingletonInterface
{
    use SingletonTrait;

    /**
     * Array $dependencies, the name/path association for each javascript file
     * @var Asset[][]
     */
    protected $dependencies = [];
    /**
     * Array $queue, internal queue used when processing dependencies
     * @var array
     */
    protected $queue = [];
    /** @var AssetServiceInterface[] */
    protected $services = [];

    /**
     * @return AssetServiceInterface[]
     */
    public function getServices()
    {
        return $this->services;
    }

    /**
     * @param AssetServiceInterface[] $services
     *
     * @return static
     */
    public function setServices($services)
    {
        $this->services = $services;

        return $this;
    }

    /**
     * Prevent external instance creation
     */
    private function __construct()
    {
        $this->dependencies = [
            'js'  => [],
            'css' => [],
        ];
        $this->queue        = [
            'js'  => [],
            'css' => [],
        ];
    }

    /**
     * @param AssetServiceInterface $assetService
     *
     * @return static
     */
    public function addAssetService(AssetServiceInterface $assetService)
    {
        $this->services[] = $assetService;

        return $this;
    }

    public function callServices()
    {
        foreach ($this->services as $service) {
            $assetsCollections = $service->getAssets();

            foreach ($assetsCollections as $type => $assets) {
                foreach ($assets as $asset) {
                    $this->registerAsset($type, $asset);
                }
            }
        }
    }

    /**
     * Add a dependency to consider
     *
     * @param string $type ('js' || 'css'), the type of asset to use
     * @param Asset  $asset
     *
     * @example
     * <code>
     * $assetsManager = AssetsManager::getInstance();
     * $assetsManager->registerAsset('js',new Asset('jquery','https://ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js',null,null,1,0));
     * $assetsManager->registerAsset('js',new Asset('flexsliderCore',WP_THEME_URL.'/js/registered/flexslider/jquery.flexslider-min.js',array('jquery'),null,1,1));
     * </code>
     * @return static
     */
    public function registerAsset($type, Asset $asset)
    {
        $dependencies                        = $this->dependencies;
        $dependencies[$type][$asset->handle] = $asset;
        $this->dependencies                  = $dependencies;

        return $this;
    }

    /**
     * add requires scripts or groups to a script or stylesheet already there
     *
     * @param string $type js or css
     * @param string $script
     * @param array  $requires
     */
    public function addRequires($type, $script, $requires)
    {
        if (!is_array($requires)) {
            $requires = [$requires];
        }

        if ($this->dependencies[$type][$script]) {
            $this->dependencies[$type][$script]->requires = array_merge($this->dependencies[$type][$script]->requires, $requires);
        }
    }

    /**
     * Get the array of dependencies (from a particular type if specififed)
     *
     * @param string $type ('js' || 'css'), the type of asset to use
     *
     * @return array
     */
    public function getDependencies($type = '')
    {
        if (in_array($type, ['js', 'css'])) {
            return $this->dependencies[$type];
        } else {
            return [];
        }
    }

    /**
     * Get a specific dependency
     *
     * @param string $type ('js' || 'css'), the type of asset to use
     * @param string $name , the dependency name
     *
     * @return Asset
     */
    public function getDependency($type, $name)
    {
        if (!array_key_exists($type, $this->dependencies) || !array_key_exists($name, $this->dependencies[$type])) {
            return null;
        }

        return $this->dependencies[$type][$name];
    }

    /**
     * Return the array of dependencies in the right order
     *
     * @param array  $toRender
     * @param string $type   , the type to get (by default js)
     * @param array  $groups array of groups to load
     *
     * @return array
     */
    public function getFlatDependencies($toRender = [], $type = 'js', $groups = [])
    {

        if (!empty($groups)) {
            $filesToRender = [];
            foreach ($groups as $group) {
                $groupFiles = $this->getDependenciesFromGroup($group);
                foreach ($groupFiles as $dep) {
                    /* @var $dep Asset */
                    if ($group != 'min' || in_array($dep->handle, $toRender)) {
                        $filesToRender[] = $dep->handle;
                    }
                }
            }
            $toRender = array_merge($toRender, $filesToRender);
        }

        $this->orderDependencies($toRender, $type);
        $this->disambiguateDependencies($type);
        $jsIndex     = $this->dependencies[$type];
        $fullQueue   = [];
        $groupsOrder = [];

        if (!empty($this->queue[$type])) {
            foreach ($this->queue[$type] as $i => $handle) {
                /* @var $handle Asset */
                if (!empty($jsIndex[$handle])) {
                    $group = $jsIndex[$handle]->concatGroup;
                    if (!isset($fullQueue[$group])) {
                        $fullQueue[$group] = [];
                    }
                    $fullQueue[$group][] = $jsIndex[$handle];
                    $groupsOrder[$group] = $i;
                }
            }
        }
        $fullQueueOrdered = [];
        asort($groupsOrder);
        foreach ($groupsOrder as $group => $i) {
            $fullQueueOrdered[$group] = $fullQueue[$group];
        }
        $this->queue[$type] = $fullQueueOrdered;

        return $this->queue[$type];
    }

    /**
     * Reorder the dependencies in the right order
     *
     * @param array  $toRender
     * @param string $type , the type to reorder (by default js)
     *
     * @return array
     */
    public function orderDependencies($toRender = [], $type = 'js')
    {
        $jsIndex = $this->dependencies[$type];
        if (!empty($toRender)) {
            foreach ($toRender as $handle) {
                /* @var $s Asset */
                if (!empty($jsIndex[$handle])) {
                    $s    = $jsIndex[$handle];
                    $deps = $s->deps;
                    if (!empty($deps)) {
                        $this->orderDependencies($deps, $type);
                    }
                    array_push($this->queue[$type], $s->handle);
                } else if (strpos($handle, 'group:') !== false) {
                    $group    = str_replace('group:', '', $handle);
                    $deps     = $this->getDependenciesFromGroup($group, $type);
                    $depsFlat = [];
                    foreach ($deps as $dep) {
                        $depsFlat[] = $dep->handle;
                    }
                    $this->orderDependencies($depsFlat, $type);
                }
            }
        }

        return $this->queue[$type];
    }

    /**
     * Remove doublons from the dependencies queue
     *
     * @param string $type , the type to disambiguate (by default js)
     *
     * @return array
     */
    public function disambiguateDependencies($type = 'js')
    {
        $this->queue[$type] = array_unique($this->queue[$type]);

        return $this->queue[$type];
    }

    /**
     * get all dependencies from a specific group
     *
     * @param string $group
     * @param string $type (js || css)
     *
     * @return Asset[] $return
     */
    public function getDependenciesFromGroup($group, $type = 'js')
    {
        $deps   = $this->dependencies[$type];
        $return = [];
        foreach ($deps as $dep) {
            /* @var $dep Asset */
            if ($dep->concatGroup == $group) {
                $return[] = $dep;
            }
        }

        return $return;
    }

    public function getGroupDependencyGroups($group, $type = 'js')
    {
        $requiredDepsHandles = [];
        $deps                = $this->getDependenciesFromGroup($group, $type);
        if (!empty($deps)) {
            foreach ($deps as $dep) {
                $requiredDepsHandles = array_merge_recursive_distinct($requiredDepsHandles, $dep->deps);
            }
        }

        $requiredGroups = $this->extractGroupDepsNamesFromDepsHandles($requiredDepsHandles, $group, $type);

        return $requiredGroups;
    }

    public function extractGroupDepsNamesFromDepsHandles($depsHandles, $group, $type = 'js')
    {
        $requiredGroups = [];

        if (!empty($depsHandles)) {
            foreach ($depsHandles as $handle) {
                if(isset($this->dependencies[$type][$handle])) {
                    $dep = $this->dependencies[$type][$handle];
                    if ($dep->concatGroup != $group) {
                        $requiredGroups[$dep->concatGroup] = $dep->concatGroup;
                    }
                }
            }
        }

        return array_keys($requiredGroups);
    }

    /**
     * Find distinct groups and their dependencies in an associative array
     *
     * @param string $dependencyType
     * @return array
     */
    public function getDistinctGroupsDependencies(string $dependencyType): array {
        return array_reduce($this->getDependencies($dependencyType), function ($acc, $asset) use ($dependencyType) {
            /** @var Asset $asset */
            if (!isset($acc[$asset->concatGroup])) {
                $acc[$asset->concatGroup] = $this->getGroupDependencyGroups($asset->concatGroup, $dependencyType);
            }

            return $acc;
        }, []);
    }
}
