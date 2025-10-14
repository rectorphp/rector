<?php

declare(strict_types=1);

use PHPParser\Node;
use PHPUnit\Runner\Version;

/**
 * The preload.php contains 2 dependencies
 *      - phpstan/phpdoc-parser
 *      - nikic/php-parser
 *
 * They need to be loaded early to avoid conflict version between rector prefixed vendor and Project vendor
 * For example, a project may use phpstan/phpdoc-parser v1, while rector uses phpstan/phpdoc-parser uses v2, that will error as class or logic are different.
 */
if (
    // verify PHPUnit is running
    defined('PHPUNIT_COMPOSER_INSTALL')

    // no need to preload if Node interface exists
    && ! interface_exists(Node::class, false)

    // load preload.php on local PHPUnit installation
    && ! class_exists(Version::class, false)

    // load preload.php only on PHPUnit 12+
    && class_exists(Version::class, true) && (int) Version::id() >= 12
) {
    require_once __DIR__ . '/preload.php';
}

// inspired by https://github.com/phpstan/phpstan/blob/master/bootstrap.php
spl_autoload_register(function (string $class): void {
    static $composerAutoloader;

    // already loaded in bin/rector.php
    if (defined('__RECTOR_RUNNING__')) {
        return;
    }

    // load prefixed or native class, e.g. for running tests
    if (strpos($class, 'RectorPrefix') === 0 || strpos($class, 'Rector\\') === 0) {
        if ($composerAutoloader === null) {
            // prefixed version autoload
            $composerAutoloader = require __DIR__ . '/vendor/autoload.php';
        }

        // some weird collision with PHPStan custom rule tests
        if (! is_int($composerAutoloader)) {
            $composerAutoloader->loadClass($class);
        }
    }
});
