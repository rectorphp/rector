<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Core\Stubs\PHPStanStubLoader;
use Rector\DowngradePhp72\Rector\ClassMethod\DowngradeParameterTypeWideningRector;
use Rector\Set\ValueObject\DowngradeLevelSetList;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

$phpStanStubLoader = new PHPStanStubLoader();
$phpStanStubLoader->loadStubs();

require_once  __DIR__ . '/../../stubs-rector/PHPUnit/Framework/TestCase.php';
require_once  __DIR__ . '/../../stubs/Composer/EventDispatcher/EventSubscriberInterface.php';
require_once  __DIR__ . '/../../stubs/Composer/Plugin/PluginInterface.php';
require_once  __DIR__ . '/../../stubs/Nette/DI/CompilerExtension.php';

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set(Option::SKIP, DowngradeRectorConfig::DEPENDENCY_EXCLUDE_PATHS);
    $parameters->set(Option::PHPSTAN_FOR_RECTOR_PATH, __DIR__ . '/phpstan-for-downgrade.neon');

    $containerConfigurator->import(DowngradeLevelSetList::DOWN_TO_PHP_71);

    $services = $containerConfigurator->services();

    $services->set(DowngradeParameterTypeWideningRector::class)
        ->configure([
            DowngradeParameterTypeWideningRector::UNSAFE_TYPES_TO_METHODS => [
                LoaderInterface::class => ['load'],
                Loader::class => ['import']
            ],
        ]);
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

        // only for dev
        'packages/Testing/PhpConfigPrinter/*',

        // Individual classes that can be excluded because
        // they are not used by Rector, and they use classes
        // loaded with "require-dev" so it'd throw an error

        // use relative paths, so files are excluded on nested directory too
        'vendor/symfony/cache/*',
        // no event-dispatcher used
        'vendor/symfony/console/Event/*',
        'vendor/symfony/console/EventListener/*',
        // only for composer patches on composer install - not needed in final package
        'vendor/cweagans/*',
        'nette/caching/src/Bridges/*',

        // This class has an issue for PHP 7.1:
        // https://github.com/rectorphp/rector/issues/4816#issuecomment-743209526
        // It doesn't happen often, and Rector doesn't use it, so then
        // we simply skip downgrading this class
        'vendor/symfony/dependency-injection/ExpressionLanguage.php',
        'vendor/symfony/dependency-injection/ExpressionLanguageProvider.php',
        'vendor/symfony/var-dumper/Caster/*',
        'vendor/symfony/console/Tester/*',
        'vendor/symfony/console/Tester/*',
        'vendor/symfony/contracts/Cache/*',
        // depends on PHPUnit, that is only in dev deps
        'vendor/myclabs/php-enum/src/PHPUnit/Comparator.php',
    ];
}
