<?php

declare(strict_types=1);

use PhpParser\NodeVisitor;
use PhpParser\NodeVisitorAbstract;
use PHPStan\BetterReflection\SourceLocator\Type\SourceLocator;
use PHPStan\PhpDocParser\Ast\Node;
use Rector\Core\Configuration\Option;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Contract\Rector\RectorInterface;
use Rector\Core\Stubs\PHPStanStubLoader;
use Rector\DowngradePhp72\Rector\ClassMethod\DowngradeParameterTypeWideningRector;
use Rector\NodeNameResolver\Contract\NodeNameResolverInterface;
use Rector\Set\ValueObject\DowngradeSetList;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\StyleInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SimplePhpDocParser\Contract\PhpDocNodeVisitorInterface;

$phpStanStubLoader = new PHPStanStubLoader();
$phpStanStubLoader->loadStubs();

require_once  __DIR__ . '/../../stubs-rector/PHPUnit/Framework/TestCase.php';

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set(Option::SKIP, DowngradeRectorConfig::DEPENDENCY_EXCLUDE_PATHS);
    $parameters->set(Option::PHPSTAN_FOR_RECTOR_PATH, __DIR__ . '/phpstan-for-downgrade.neon');

    $containerConfigurator->import(DowngradeSetList::PHP_80);
    $containerConfigurator->import(DowngradeSetList::PHP_74);
    $containerConfigurator->import(DowngradeSetList::PHP_73);
    $containerConfigurator->import(DowngradeSetList::PHP_72);

    $services = $containerConfigurator->services();
    $services->set(DowngradeParameterTypeWideningRector::class)
        ->call('configure', [[
            DowngradeParameterTypeWideningRector::SAFE_TYPES => [
                RectorInterface::class,
                NodeVisitorAbstract::class,
                NodeVisitor::class,
                ConfigurableRectorInterface::class,
                OutputInterface::class,
                StyleInterface::class,
                PhpDocNodeVisitorInterface::class,
                Node::class,
                NodeNameResolverInterface::class,
                // phpstan
                SourceLocator::class,
                \PHPStan\PhpDocParser\Ast\Node::class,
                \PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode::class,
                \PHPStan\PhpDocParser\Ast\NodeAttributes::class,
                \PhpParser\Parser::class,
                \Rector\Naming\Contract\RenameParamValueObjectInterface::class,
                \Symplify\RuleDocGenerator\Contract\RuleCodeSamplePrinterInterface::class,
                \Symplify\RuleDocGenerator\Contract\Category\CategoryInfererInterface::class,
                \PhpParser\PrettyPrinterAbstract::class,
            ],
            DowngradeParameterTypeWideningRector::SAFE_TYPES_TO_METHODS => [
                ContainerInterface::class => [
                    'setParameter',
                    'getParameter',
                    'hasParameter',
                ],
            ],
        ]]);
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
        // depends on PHPUnit, that is only in dev deps
        'vendor/myclabs/php-enum/src/PHPUnit/Comparator.php',
    ];
}
