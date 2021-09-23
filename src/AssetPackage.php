<?php


namespace WonderWp\Component\Asset;

use Symfony\Component\Asset\Package;
use Symfony\Component\Asset\VersionStrategy\VersionStrategyInterface;

class AssetPackage extends Package
{
    private $baseUrl;
    private $basePath;

    public function __construct(VersionStrategyInterface $versionStrategy, array $opts = [])
    {
        parent::__construct($versionStrategy);

        $this->setBaseUrl(isset($opts['baseUrl']) ? $opts['baseUrl'] : null);

        $this->initBasePath($opts);
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
