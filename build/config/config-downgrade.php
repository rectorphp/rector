<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Core\Stubs\PHPStanStubLoader;
use Rector\DowngradePhp71\Rector\FunctionLike\DowngradeNullableTypeDeclarationRector;
use Rector\Set\ValueObject\DowngradeSetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

$phpStanStubLoader = new PHPStanStubLoader();
$phpStanStubLoader->loadStubs();

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $skip = array_merge(
        DowngradeRectorConfig::DEPENDENCY_EXCLUDE_PATHS,
        [
            // should be skipped until phpstan is downgraded to avoid conflict like this https://github.com/rectorphp/rector-prefixed/runs/2422176105#step:4:4
            DowngradeNullableTypeDeclarationRector::class
        ]
    );
    $parameters->set(Option::SKIP, $skip);

    $parameters->set(Option::PHPSTAN_FOR_RECTOR_PATH, __DIR__ . '/phpstan-for-downgrade.neon');

    $parameters->set(Option::SETS, [
        DowngradeSetList::PHP_80,
        DowngradeSetList::PHP_74,
        DowngradeSetList::PHP_73,
        DowngradeSetList::PHP_72,
        DowngradeSetList::PHP_71,
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

        // missing phpunit test case
        'packages/Testing/PHPUnit/AbstractRectorTestCase.php',
        'packages/Testing/PHPUnit/AbstractComposerRectorTestCase.php',

        // only for dev
        'packages/Testing/PhpConfigPrinter/*',

        // Individual classes that can be excluded because
        // they are not used by Rector, and they use classes
        // loaded with "require-dev" so it'd throw an error

        // use relative paths, so files are excluded on nested directory too
        'vendor/symfony/http-kernel/HttpKernelBrowser.php',
        'vendor/symfony/http-foundation/Session/*',
        'vendor/symfony/string/Slugger/AsciiSlugger.php',
        'vendor/symfony/cache/*',
        'nette/caching/src/Bridges/*',

        // This class has an issue for PHP 7.1:
        // https://github.com/rectorphp/rector/issues/4816#issuecomment-743209526
        // It doesn't happen often, and Rector doesn't use it, so then
        // we simply skip downgrading this class
        'vendor/symfony/dependency-injection/ExpressionLanguage.php',
        'vendor/symfony/dependency-injection/ExpressionLanguageProvider.php',
        'vendor/symfony/var-dumper/Caster/*',
    ];
}
