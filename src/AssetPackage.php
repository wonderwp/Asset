<?php


namespace WonderWp\Component\Asset;

use LogicException;
use Symfony\Component\Asset\Package;
use Symfony\Component\Asset\VersionStrategy\VersionStrategyInterface;
use WonderWp\Component\Asset\Exception\AssetPackageAvailableGroupsException;

class AssetPackage extends Package
{
    private $name;
    private $baseUrl;
    private $basePath;
    private $availableAssetTypes = [];
    private $concernedAssetTypes = [];

    public function __construct($name, VersionStrategyInterface $versionStrategy, array $availableAssetTypes, array $opts = [])
    {
        parent::__construct($versionStrategy);

        $this->name = $name;

        $this->availableAssetTypes = $availableAssetTypes;

        $this->setBaseUrl(isset($opts['baseUrl']) ? $opts['baseUrl'] : null);

        $this->initBasePath($opts);

        if (isset($opts['assetTypes'])) {
            $containOnlyAuthorizedGroups = !count(array_diff($opts['assetTypes'], $this->availableAssetTypes));

            if (!$containOnlyAuthorizedGroups) {
                throw new AssetPackageAvailableGroupsException('You must provide asset group according to available groups : ' . join(', ', $this->availableAssetTypes));
            }

            $this->concernedAssetTypes = $opts['assetTypes'];
        }
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Returns the base path.
     *
     * @return string The base path
     */
    public function getBasePath(): string
    {
        return $this->basePath;
    }

    /**
     * {@inheritdoc}
     */
    public function getUrl(string $path): string
    {
        if ($this->isAbsoluteUrl($path)) {
            return $path;
        }

        $versionedPath = $this->getVersionStrategy()->applyVersion($this->getBasePath() . $path);

        if ($this->isAbsoluteUrl($versionedPath)) {
            return $versionedPath;
        }

        if ($versionedPath && '/' != $versionedPath[0]) {
            $versionedPath = '/' . $versionedPath;
        }

        return $this->getBaseUrl() . $versionedPath;
    }

    /**
     * Get path without baseUrl, useful to find file
     */
    public function getFullPath(string $path): string
    {
        if ($this->isAbsoluteUrl($path)) {
            return $path;
        }

        return $this->getVersionStrategy()->applyVersion($this->getBasePath() . $path);
    }

    /**
     * Returns the base URL
     *
     * @return string The base URL
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * @param string $assetType One into 'js', 'css', 'critical'
     * @return bool
     */
    public function isAssetTypeConcerned(string $assetType): bool
    {
        return in_array($assetType, $this->concernedAssetTypes, true);
    }

    public function setBaseUrl($baseUrl)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    /**
     * @param array $opts
     * @return void
     */
    private function initBasePath(array $opts): void
    {
        if (!isset($opts['basePath']) || empty($opts['basePath'])) {
            $this->basePath = '/';
        } else {
            $basePath = $opts['basePath'];
            if ('/' != $basePath[0]) {
                $basePath = '/' . $basePath;
            }

            $this->basePath = rtrim($basePath, '/') . '/';
        }
    }
}
