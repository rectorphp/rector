<?php

declare(strict_types = 1);

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
        $composerAutoloader->loadClass($class);
    }

    // aliased by php-scoper, that's why its missing
    if ($class === 'Symplify\SmartFileSystem\SmartFileInfo') {
        $filePath = __DIR__ . '/vendor/symplify/smart-file-system/src/SmartFileInfo.php';
        if (file_exists($filePath)) {
            require $filePath;
        }
    }

    if ($class === 'Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator') {
        // avoid duplicated autoload bug in Rector demo runner
        if (class_exists('Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator', false)) {
            return;
        }
    }
});

if (! interface_exists('UnitEnum')) {
    /**
     * @since 8.1
     */
    interface UnitEnum
    {
        /**
         * @return static[]
         */
        public static function cases(): array;
    }
}

if (! interface_exists('BackedEnum')) {
    /**
     * @since 8.1
     */
    interface BackedEnum extends UnitEnum {
        /**
         * @param int|string $value
         * @return $this
         */
        public static function from($value);

        /**
         * @param int|string $value
         * @return $this|null
         */
        public static function tryFrom($value);
    }
}
