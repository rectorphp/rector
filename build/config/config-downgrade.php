<?php

declare(strict_types=1);

use PhpParser\NodeVisitor;
use PhpParser\NodeVisitorAbstract;
use PHPStan\BetterReflection\SourceLocator\Type\SourceLocator;
use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\Type\Type;
use Rector\Core\Configuration\Option;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Contract\Rector\RectorInterface;
use Rector\Core\Stubs\PHPStanStubLoader;
use Rector\DowngradePhp72\Rector\ClassMethod\DowngradeParameterTypeWideningRector;
use Rector\NodeNameResolver\Contract\NodeNameResolverInterface;
use Rector\Set\ValueObject\DowngradeLevelSetList;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\StyleInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SimplePhpDocParser\Contract\PhpDocNodeVisitorInterface;

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
        ->call('configure', [[
            DowngradeParameterTypeWideningRector::SAFE_TYPES => [
                // phsptan
                Type::class,
                RectorInterface::class,
                // php-parser
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
                \Helmich\TypoScriptParser\Parser\Traverser\Visitor::class,
                \Symplify\SymplifyKernel\Contract\LightKernelInterface::class,
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
        'vendor/symfony/string/Slugger/AsciiSlugger.php',
        'vendor/symfony/cache/*',
        // no event-dispatcher used
        'vendor/symfony/console/Event/*',
        'vendor/symfony/console/EventListener/*',
        'nette/caching/src/Bridges/*',

        // This class has an issue for PHP 7.1:
        // https://github.com/rectorphp/rector/issues/4816#issuecomment-743209526
        // It doesn't happen often, and Rector doesn't use it, so then
        // we simply skip downgrading this class
        'vendor/symfony/dependency-injection/ExpressionLanguage.php',
        'vendor/symfony/dependency-injection/ExpressionLanguageProvider.php',
        'vendor/symfony/var-dumper/Caster/*',
        'vendor/symfony/console/Tester/*',
        'vendor/symfony/contracts/Cache/*',
        // depends on PHPUnit, that is only in dev deps
        'vendor/myclabs/php-enum/src/PHPUnit/Comparator.php',
    ];
}
