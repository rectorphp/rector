<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\DowngradeLevelSetList;

require_once  __DIR__ . '/../target-repository/stubs-rector/PHPUnit/Framework/TestCase.php';
require_once  __DIR__ . '/../../stubs/Composer/EventDispatcher/EventSubscriberInterface.php';
require_once  __DIR__ . '/../../stubs/Composer/Plugin/PluginInterface.php';
require_once  __DIR__ . '/../../stubs/Nette/DI/CompilerExtension.php';

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->parallel();

    $rectorConfig->skip(DowngradeRectorConfig::DEPENDENCY_EXCLUDE_PATHS);

    $rectorConfig->phpstanConfig(__DIR__ . '/phpstan-for-downgrade.neon');

    $rectorConfig->import(DowngradeLevelSetList::DOWN_TO_PHP_72);
};

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
        // symfony test are parts of package
        '*/Test/*',

        // Individual classes that can be excluded because
        // they are not used by Rector, and they use classes
        // loaded with "require-dev" so it'd throw an error

        // use relative paths, so files are excluded on nested directory too
        'vendor/symfony/cache/*',
        // only for composer patches on composer install - not needed in final package
        'vendor/cweagans/*',
        // Rector doesn't use it, so we simply skip downgrading this class
        'vendor/symfony/contracts/Cache/*',

        'vendor/rector/rector-generator/templates',
    ];
}
