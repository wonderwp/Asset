<?php

declare(strict_types=1);

// Prefer host autoload when this package is installed in a project (avoids
// loading a nested package vendor/ PHPUnit that conflicts with the host binary).
$candidates = [
    dirname(__DIR__, 3) . '/autoload.php',
    dirname(__DIR__) . '/vendor/autoload.php',
];

$autoload = null;
foreach ($candidates as $candidate) {
    if (is_file($candidate)) {
        $autoload = $candidate;
        break;
    }
}

if ($autoload === null) {
    fwrite(STDERR, "Unable to locate Composer autoload.php for wonderwp/asset unit tests.\n");
    exit(1);
}

require_once $autoload;

$wpDependencyCandidates = [
    dirname(__DIR__) . '/wordpress/wp-includes/class-wp-dependency.php',
    dirname(__DIR__, 4) . '/web/wp/wp-includes/class-wp-dependency.php',
];

$wpDependencyLoaded = false;
foreach ($wpDependencyCandidates as $wpDependency) {
    if (is_file($wpDependency)) {
        require_once $wpDependency;
        $wpDependencyLoaded = true;
        break;
    }
}

if (!$wpDependencyLoaded) {
    require_once __DIR__ . '/stub-wp-dependency.php';
}

require_once __DIR__ . '/stub.php';
