<?php

declare(strict_types=1);

/**
 * Configuration consts for the different rector.php config files
 */
final class DowngradeRectorConfig
{
    /**
     * Exclude paths when downgrading a dependency
     */
    public const DEPENDENCY_EXCLUDE_PATHS = [
        '*/tests/*',
        // Individual classes that can be excluded because
        // they are not used by Rector, and they use classes
        // loaded with "require-dev" so it'd throw an error
        __DIR__ . '/../../vendor/symfony/cache/DoctrineProvider.php',
        __DIR__ . '/../../vendor/symfony/cache/Messenger/EarlyExpirationHandler.php',
        __DIR__ . '/../../vendor/symfony/http-kernel/HttpKernelBrowser.php',
        __DIR__ . '/../../vendor/symfony/string/Slugger/AsciiSlugger.php',
    ];
    /**
     * Exclude paths when downgrading the Rector source code
     */
    public const RECTOR_EXCLUDE_PATHS = [
        // '/Source/',
        // '/*Source/',
        // '/Fixture/',
        // '/Expected/',
        __DIR__ . '/../../packages/doctrine-annotation-generated/src/*',
        __DIR__ . '/../../packages/rector-generator/templates/*',
        __DIR__ . '/../../vendor/*',
        __DIR__ . '/../../ci/*',
        __DIR__ . '/../../compiler/*',
        __DIR__ . '/../../stubs/*',
        '*/tests/*',
        // '*.php.inc',
    ];
}
