<?php

namespace WonderWp\Component\Asset;

interface AssetExporterInterface
{
    /**
     * @param $args
     */
    public function __invoke($args);

    public function export();
}
