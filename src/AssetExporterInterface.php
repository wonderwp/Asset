<?php

namespace WonderWp\Component\Asset;

interface AssetExporterInterface
{
    /**
     * @param $args
     */
    public function __invoke($args, $assoc_args);

    public function export();
}
