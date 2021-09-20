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
