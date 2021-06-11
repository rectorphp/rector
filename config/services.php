<?php

declare(strict_types=1);

use Composer\Semver\VersionParser;
use Doctrine\Inflector\Inflector;
use Doctrine\Inflector\Rules\English\InflectorFactory;
use Ergebnis\Json\Printer\Printer;
use Ergebnis\Json\Printer\PrinterInterface;
use Idiosyncratic\EditorConfig\EditorConfig;
use PhpParser\BuilderFactory;
use PhpParser\Lexer;
use PhpParser\NodeFinder;
use PhpParser\NodeVisitor\CloningVisitor;
use PhpParser\NodeVisitor\NodeConnectingVisitor;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use PHPStan\Analyser\NodeScopeResolver;
use PHPStan\Analyser\ScopeFactory;
use PHPStan\Dependency\DependencyResolver;
use PHPStan\File\FileHelper;
use PHPStan\PhpDoc\TypeNodeResolver;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TypeParser;
use PHPStan\Reflection\ReflectionProvider;
use Rector\BetterPhpDocParser\PhpDocParser\BetterPhpDocParser;
use Rector\BetterPhpDocParser\PhpDocParser\BetterTypeParser;
use Rector\Caching\CacheFactory;
use Rector\Core\Console\ConsoleApplication;
use Rector\Core\PhpParser\Parser\NikicPhpParserFactory;
use Rector\Core\PhpParser\Parser\PhpParserLexerFactory;
use Rector\NodeTypeResolver\DependencyInjection\PHPStanServicesFactory;
use Rector\NodeTypeResolver\Reflection\BetterReflection\SourceLocator\IntermediateSourceLocator;
use Rector\NodeTypeResolver\Reflection\BetterReflection\SourceLocatorProvider\DynamicSourceLocatorProvider;
use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;
use Symplify\PackageBuilder\Console\Command\CommandNaming;
use Symplify\PackageBuilder\Console\Style\SymfonyStyleFactory;
use Symplify\PackageBuilder\Parameter\ParameterProvider;
use Symplify\PackageBuilder\Php\TypeChecker;
use Symplify\PackageBuilder\Reflection\PrivatesAccessor;
use Symplify\PackageBuilder\Reflection\PrivatesCaller;
use Symplify\PackageBuilder\Strings\StringFormatConverter;
use Symplify\SmartFileSystem\FileSystemFilter;
use Symplify\SmartFileSystem\FileSystemGuard;
use Symplify\SmartFileSystem\Finder\FinderSanitizer;
use Symplify\SmartFileSystem\Json\JsonFileSystem;
use Symplify\SmartFileSystem\SmartFileSystem;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->public()
        ->autowire()
        ->autoconfigure();

    $services->load('Rector\Core\\', __DIR__ . '/../src')
        ->exclude([
            __DIR__ . '/../src/Rector',
            __DIR__ . '/../src/Exception',
            __DIR__ . '/../src/DependencyInjection/CompilerPass',
            __DIR__ . '/../src/DependencyInjection/Loader',
            __DIR__ . '/../src/HttpKernel',
            __DIR__ . '/../src/ValueObject',
            __DIR__ . '/../src/Bootstrap',
            __DIR__ . '/../src/PhpParser/Node/CustomNode',
            __DIR__ . '/../src/functions',
            __DIR__ . '/../src/constants.php',
            __DIR__ . '/../src/PhpParser/NodeVisitor/CreatedByRuleNodeVisitor.php',
        ]);

    $services->alias(SymfonyApplication::class, ConsoleApplication::class);

    $services->set(FileSystemGuard::class);

    $services->set(SimpleCallableNodeTraverser::class);

    $services->set(ParserFactory::class);
    $services->set(BuilderFactory::class);
    $services->set(CloningVisitor::class);
    $services->set(NodeFinder::class);

    $services->set(Parser::class)
        ->factory([service(NikicPhpParserFactory::class), 'create']);
    $services->set(Lexer::class)
        ->factory([service(PhpParserLexerFactory::class), 'create']);

    // symplify/package-builder
    $services->set(PrivatesAccessor::class);
    $services->set(PrivatesCaller::class);
    $services->set(FinderSanitizer::class);
    $services->set(FileSystemFilter::class);

    $services->set(ParameterProvider::class)
        ->arg('$container', service('service_container'));

    $services->set(CommandNaming::class);
    $services->set(SmartFileSystem::class);

    $services->set(StringFormatConverter::class);

    $services->set(SymfonyStyleFactory::class);
    $services->set(SymfonyStyle::class)
        ->factory([service(SymfonyStyleFactory::class), 'create']);

    $services->set(JsonFileSystem::class);
    $services->set(NodeConnectingVisitor::class);

    $services->set(InflectorFactory::class);
    $services->set(Inflector::class)
        ->factory([service(InflectorFactory::class), 'build']);

    $services->set(VersionParser::class);
    $services->set(TypeChecker::class);

    // phpdoc parser
    $services->set(\PHPStan\PhpDocParser\Lexer\Lexer::class);
    $services->alias(PhpDocParser::class, BetterPhpDocParser::class);

    // cache
    $services->set(DependencyResolver::class)
        ->factory([service(PHPStanServicesFactory::class), 'createDependencyResolver']);
    $services->set(FileHelper::class)
        ->factory([service(PHPStanServicesFactory::class), 'createFileHelper']);

    $services->set(\Rector\Caching\Cache::class)
        ->factory([service(CacheFactory::class), 'create']);

    // type resolving
    $services->set(IntermediateSourceLocator::class);
    $services->alias(TypeParser::class, BetterTypeParser::class);

    // PHPStan services
    $services->set(ReflectionProvider::class)
        ->factory([service(PHPStanServicesFactory::class), 'createReflectionProvider']);
    $services->set(NodeScopeResolver::class)
        ->factory([service(PHPStanServicesFactory::class), 'createNodeScopeResolver']);
    $services->set(ScopeFactory::class)
        ->factory([service(PHPStanServicesFactory::class), 'createScopeFactory']);
    $services->set(TypeNodeResolver::class)
        ->factory([service(PHPStanServicesFactory::class), 'createTypeNodeResolver']);
    $services->set(DynamicSourceLocatorProvider::class)
        ->factory([service(PHPStanServicesFactory::class), 'createDynamicSourceLocatorProvider']);

    $services->set(Printer::class);
    $services->alias(PrinterInterface::class, Printer::class);

    $services->set(EditorConfig::class);
};
