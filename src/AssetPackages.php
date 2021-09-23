<?php


namespace WonderWp\Component\Asset;


use Symfony\Component\Asset\Exception\InvalidArgumentException;
use Symfony\Component\Asset\Exception\LogicException;
use Symfony\Component\Asset\PackageInterface;
use Symfony\Component\Asset\Packages;
use WonderWp\Component\Asset\Exception\PackageReservedNameException;

class AssetPackages
{
    /**
     * @var PackageInterface|null
     */
    private $defaultPackage;
    private $packages = [];

    public function __construct(PackageInterface $defaultPackage = null, iterable $packages = [])
    {
        $this->defaultPackage = $defaultPackage;

        foreach ($packages as $name => $package) {
            if ($name === 'default') {
                throw new PackageReservedNameException('The package name \'default\' is reserved for default package');
            }

            $this->addPackage($name, $package);
        }
    }

    public function setDefaultPackage(PackageInterface $defaultPackage)
    {
        $this->defaultPackage = $defaultPackage;
    }

    public function addPackage(string $name, PackageInterface $package)
    {
        $this->packages[$name] = $package;
    }

    public function getPackages(): array
    {
        if ($this->defaultPackage !== null) {
            return ['default' => $this->defaultPackage] + $this->packages;
        }

        return $this->packages;
    }

    /**
     * Returns an asset package.
     *
     * @param string $name The name of the package or null for the default package
     *
     * @return PackageInterface An asset package
     *
     * @throws InvalidArgumentException If there is no package by that name
     * @throws LogicException           If no default package is defined
     */
    public function getPackage(string $name = null)
    {
        if (null === $name) {
            if (null === $this->defaultPackage) {
                throw new LogicException('There is no default asset package, configure one first.');
            }

            return $this->defaultPackage;
        }

        if (!isset($this->packages[$name])) {
            throw new InvalidArgumentException(sprintf('There is no "%s" asset package.', $name));
        }

        return $this->packages[$name];
    }

    /**
     * Gets the version to add to public URL.
     *
     * @param string $path        A public path
     * @param string $packageName A package name
     *
     * @return string The current version
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
     * @param string $path        A public path
     * @param string $packageName The name of the asset package to use
     *
     * @return string A public path which takes into account the base path and URL path
     */
    public function getUrl(string $path, string $packageName = null)
    {
        return $this->getPackage($packageName)->getUrl($path);
    }
}
