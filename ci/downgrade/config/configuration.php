<?php

declare(strict_types=1);

use Rector\Core\Stubs\PHPStanStubLoader;

$phpStanStubLoader = new PHPStanStubLoader();
$phpStanStubLoader->loadStubs();

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
        __DIR__ . '/../../../vendor/symfony/cache/DoctrineProvider.php',
        __DIR__ . '/../../../vendor/symfony/cache/Messenger/EarlyExpirationHandler.php',
        __DIR__ . '/../../../vendor/symfony/http-kernel/HttpKernelBrowser.php',
        __DIR__ . '/../../../vendor/symfony/string/Slugger/AsciiSlugger.php',
        // This class has an issue for PHP 7.1:
        // https://github.com/rectorphp/rector/issues/4816#issuecomment-743209526
        // It doesn't happen often, and Rector doesn't use it, so then
        // we simply skip downgrading this class
        __DIR__ . '/../../../vendor/symfony/cache/Adapter/CouchbaseBucketAdapter.php',
    ];
    /**
     * Exclude paths when downgrading the Rector source code
     */
    public const RECTOR_EXCLUDE_PATHS = [
        '*/tests/*',
        __DIR__ . '/../../../packages/rector-generator/templates/*',
        __DIR__ . '/../../../vendor/*',
        __DIR__ . '/../../../ci/*',
        __DIR__ . '/../../../stubs/*',
    ];
}
