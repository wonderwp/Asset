<?php


namespace WonderWp\Component\Asset;


use Symfony\Component\Asset\Exception\InvalidArgumentException;
use Symfony\Component\Asset\Exception\LogicException;
use Symfony\Component\Asset\PackageInterface;
use Symfony\Component\Asset\Packages;

class AssetPackages
{
    private $packages = [];

    /**
     * @param PackageInterface[] $packages Additional packages indexed by name
     */
    public function __construct($packages = [])
    {
        foreach ($packages as $name => $package) {
            $this->addPackage($name, $package);
        }
    }

    public function getPackages()
    {
        return $this->packages;
    }

    public function getPackagesBy($assetType)
    {
        return array_filter($this->packages, function($package) use ($assetType) {
            /** @var $package AssetPackage */
            return $package->isAssetTypeConcerned($assetType);
        });
    }

    public function addPackage(string $name, PackageInterface $package)
    {
        $this->packages[$name] = $package;
    }

    /**
     * Returns an asset package.
     *
     * @param string $name The name of the package or null for the default package
     *
     * @return PackageInterface
     *
     * @throws InvalidArgumentException If there is no package by that name
     */
    public function getPackage(string $name)
    {
        if (!isset($this->packages[$name])) {
            throw new InvalidArgumentException(sprintf('There is no "%s" asset package.', $name));
        }

        return $this->packages[$name];
    }

    /**
     * Gets the version to add to public URL.
     *
     * @param string $path A public path
     * @param string $packageName A package name
     *
     * @return string
     */
    public function getVersion(string $path, string $packageName = null)
    {
        return $this->getPackage($packageName)->getVersion($path);
    }

    /**
     * Returns the public path.
     *
     * Absolute paths (i.e. http://...) are returned unmodified.
     *
     * @param string $path A public path
     * @param string $packageName The name of the asset package to use
     *
     * @return string A public path which takes into account the base path and URL path
     */
    public function getUrl(string $path, string $packageName = null)
    {
        return $this->getPackage($packageName)->getUrl($path);
    }
}
