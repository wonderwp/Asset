<?php
/**
 * Integration smoke: wonderwp/asset is autoloadable under a real WordPress.
 *
 *   wp eval-file vendor/wonderwp/asset/tests/Integration/smoke-autoload.php
 */

use WonderWp\Component\Asset\DirectAssetEnqueuer;

if (!defined('ABSPATH')) {
    fwrite(STDERR, "WordPress is not loaded. Run via WP-CLI from a project that requires wonderwp/asset.\n");
    exit(1);
}

$autoloadCandidates = [
    dirname(__DIR__, 4) . '/autoload.php',
    dirname(__DIR__, 2) . '/vendor/autoload.php',
];
foreach ($autoloadCandidates as $autoload) {
    if (is_file($autoload)) {
        require_once $autoload;
        break;
    }
}

if (!class_exists(DirectAssetEnqueuer::class)) {
    fwrite(STDERR, "FAIL wonderwp/asset DirectAssetEnqueuer not autoloadable\n");
    exit(1);
}

fwrite(STDOUT, "PASS DirectAssetEnqueuer autoloadable\n");
fwrite(STDOUT, "OK wonderwp/asset Integration smoke-autoload\n");
