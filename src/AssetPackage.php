<?php


namespace WonderWp\Component\Asset;

use LogicException;
use Symfony\Component\Asset\Package;
use Symfony\Component\Asset\VersionStrategy\VersionStrategyInterface;

class AssetPackage extends Package
{
    private $name;
    private $baseUrl;
    private $basePath;
    private $availableAssetTypes = ['js', 'css', 'critical'];
    private $concernedAssetTypes = ['js', 'css', 'critical'];

    public function __construct($name, VersionStrategyInterface $versionStrategy, array $opts = [])
    {
        parent::__construct($versionStrategy, null);

        $this->name = $name;

        $this->setBaseUrl(isset($opts['baseUrl']) ? $opts['baseUrl']: null);

        if (!isset($opts['basePath']) || empty($opts['basePath'])) {
            $this->basePath = '/';
        } else {
            $basePath = $opts['basePath'];
            if ('/' != $basePath[0]) {
                $basePath = '/' . $basePath;
            }

            $this->basePath = rtrim($basePath, '/') . '/';
        }

        if (isset($opts['assetTypes'])) {
            $containOnlyAuthorizedGroups = !count(array_diff($opts['assetTypes'], $this->availableAssetTypes));

            if (!$containOnlyAuthorizedGroups) {
                throw new LogicException('You must provide asset group according to available groups : ' . join(', ', $this->availableAssetTypes));
            }

            $this->concernedAssetTypes = $opts['assetTypes'];
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the base path.
     *
     * @return string The base path
     */
    public function getBasePath()
    {
        return $this->basePath;
    }

    /**
     * {@inheritdoc}
     */
    public function getUrl(string $path)
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
     * Returns the base URL
     *
     * @return string The base URL
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    public function isAssetTypeConcerned($assetType)
    {
        return in_array($assetType, $this->concernedAssetTypes, true);
    }

    public function setBaseUrl($baseUrl)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
    }
}
