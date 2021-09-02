<?php

namespace WonderWp\Component\Asset;

class WordpressAssetGateway
{
    public function registerStyle(...$args)
    {
        return \wp_register_style(...$args);
    }

    public function registerScript(...$args)
    {
        return \wp_register_script(...$args);
    }

    public function enqueueStyle(...$args)
    {
        return \wp_enqueue_style(...$args);
    }

    public function enqueueScript(...$args)
    {
        return \wp_enqueue_script(...$args);
    }

    public function applyFilters(...$args)
    {
        return \apply_filters(...$args);
    }

    public function isAdmin(...$args) {
        return \is_admin(...$args);
    }
}
