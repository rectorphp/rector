<?php

declare(strict_types=1);

use Composer\Semver\VersionParser;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Inflector\Inflector;
use Doctrine\Inflector\Rules\English\InflectorFactory;
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
use PHPStan\Reflection\ReflectionProvider;
use Psr\Cache\CacheItemPoolInterface;
use Psr\SimpleCache\CacheInterface;
use Rector\BetterPhpDocParser\PhpDocParser\BetterPhpDocParser;
use Rector\Caching\Cache\Adapter\FilesystemAdapterFactory;
use Rector\Core\Bootstrap\NoRectorsLoadedReporter;
use Rector\Core\Console\ConsoleApplication;
use Rector\Core\PhpParser\Parser\NikicPhpParserFactory;
use Rector\Core\PhpParser\Parser\PhpParserLexerFactory;
use Rector\DoctrineAnnotationGenerated\ConstantPreservingAnnotationReader;
use Rector\NodeTypeResolver\DependencyInjection\PHPStanServicesFactory;
use Rector\NodeTypeResolver\Reflection\BetterReflection\SourceLocator\IntermediateSourceLocator;
use Rector\NodeTypeResolver\Reflection\BetterReflection\SourceLocatorProvider\DynamicSourceLocatorProvider;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use Symfony\Component\Cache\Psr16Cache;
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
        ]);

    $services->load('Rector\\', __DIR__ . '/../packages')
        ->exclude([
            // @todo move to value object
            __DIR__ . '/../packages/AttributeAwarePhpDoc/Ast',
            __DIR__ . '/../packages/BetterPhpDocParser/Attributes/Ast/PhpDoc',
            __DIR__ . '/../packages/BetterPhpDocParser/Attributes/Attribute',
            __DIR__ . '/../packages/BetterPhpDocParser/PhpDocInfo/PhpDocInfo.php',
            __DIR__ . '/../packages/*/{ValueObject,Contract,Exception}',
            __DIR__ . '/../packages/NodeTypeResolver/Reflection/BetterReflection/RectorBetterReflectionSourceLocatorFactory.php',
            __DIR__ . '/../packages/Testing/PHPUnit/Runnable/NodeVisitor',
            __DIR__ . '/../packages/Testing/PHPUnit',
        ]);

    $services->alias(SymfonyApplication::class, ConsoleApplication::class);

    $services->set(NoRectorsLoadedReporter::class);
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
    $services->alias(Reader::class, ConstantPreservingAnnotationReader::class);

    // cache
    $services->set(DependencyResolver::class)
        ->factory([service(PHPStanServicesFactory::class), 'createDependencyResolver']);
    $services->set(FileHelper::class)
        ->factory([service(PHPStanServicesFactory::class), 'createFileHelper']);
    $services->set(Psr16Cache::class);
    $services->alias(CacheInterface::class, Psr16Cache::class);
    $services->set(FilesystemAdapter::class)
        ->factory([service(FilesystemAdapterFactory::class), 'create']);
    $services->set(TagAwareAdapter::class)
        ->arg('$itemsPool', service(FilesystemAdapter::class));
    $services->alias(CacheItemPoolInterface::class, FilesystemAdapter::class);
    $services->alias(TagAwareAdapterInterface::class, TagAwareAdapter::class);

    // type resolving
    $services->set(IntermediateSourceLocator::class);

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
};
