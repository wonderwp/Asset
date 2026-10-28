<?php

namespace WonderWp\Component\Asset\Tests;

class WP_Filesystem_Direct {
    public function get_contents( $file ) {
        return @file_get_contents( $file );
    }

    public function exists($file) {
        return @file_exists( $file );
    }
}

namespace WonderWp\Component\PluginSkeleton;

interface ManagerInterface {}

interface ManagerAwareInterface
{
    public function getManager(): ManagerInterface;
    public function setManager(ManagerInterface $manager): static;
}

trait ManagerAwareTrait
{
    protected $manager;

    public function getManager(): ManagerInterface
    {
        return $this->manager;
    }

    public function setManager(ManagerInterface $manager): static
    {
        $this->manager = $manager;

        return $this;
    }
}

abstract class AbstractManager implements ManagerInterface {}
