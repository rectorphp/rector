<?php

declare (strict_types=1);
namespace RectorPrefix20220606;

use RectorPrefix20220606\Composer\Semver\VersionParser;
use RectorPrefix20220606\Doctrine\Inflector\Inflector;
use RectorPrefix20220606\Doctrine\Inflector\Rules\English\InflectorFactory;
use RectorPrefix20220606\OndraM\CiDetector\CiDetector;
use RectorPrefix20220606\PhpParser\BuilderFactory;
use RectorPrefix20220606\PhpParser\Lexer;
use RectorPrefix20220606\PhpParser\NodeFinder;
use RectorPrefix20220606\PhpParser\NodeVisitor\CloningVisitor;
use RectorPrefix20220606\PhpParser\NodeVisitor\NodeConnectingVisitor;
use RectorPrefix20220606\PHPStan\Analyser\NodeScopeResolver;
use RectorPrefix20220606\PHPStan\Analyser\ScopeFactory;
use RectorPrefix20220606\PHPStan\Dependency\DependencyResolver;
use RectorPrefix20220606\PHPStan\File\FileHelper;
use RectorPrefix20220606\PHPStan\Parser\Parser;
use RectorPrefix20220606\PHPStan\PhpDoc\TypeNodeResolver;
use RectorPrefix20220606\PHPStan\PhpDocParser\Parser\PhpDocParser;
use RectorPrefix20220606\PHPStan\PhpDocParser\Parser\TypeParser;
use RectorPrefix20220606\PHPStan\Reflection\ReflectionProvider;
use RectorPrefix20220606\Rector\BetterPhpDocParser\PhpDocParser\BetterPhpDocParser;
use RectorPrefix20220606\Rector\BetterPhpDocParser\PhpDocParser\BetterTypeParser;
use RectorPrefix20220606\Rector\Caching\Cache;
use RectorPrefix20220606\Rector\Caching\CacheFactory;
use RectorPrefix20220606\Rector\Caching\ValueObject\Storage\MemoryCacheStorage;
use RectorPrefix20220606\Rector\Config\RectorConfig;
use RectorPrefix20220606\Rector\Core\Bootstrap\ExtensionConfigResolver;
use RectorPrefix20220606\Rector\Core\Console\ConsoleApplication;
use RectorPrefix20220606\Rector\Core\Console\Style\RectorConsoleOutputStyle;
use RectorPrefix20220606\Rector\Core\Console\Style\RectorConsoleOutputStyleFactory;
use RectorPrefix20220606\Rector\Core\Validation\Collector\EmptyConfigurableRectorCollector;
use RectorPrefix20220606\Rector\NodeTypeResolver\DependencyInjection\PHPStanServicesFactory;
use RectorPrefix20220606\Rector\NodeTypeResolver\Reflection\BetterReflection\SourceLocator\IntermediateSourceLocator;
use RectorPrefix20220606\Rector\NodeTypeResolver\Reflection\BetterReflection\SourceLocatorProvider\DynamicSourceLocatorProvider;
use RectorPrefix20220606\Rector\PSR4\Composer\PSR4NamespaceMatcher;
use RectorPrefix20220606\Rector\PSR4\Contract\PSR4AutoloadNamespaceMatcherInterface;
use RectorPrefix20220606\Symfony\Component\Console\Application;
use function RectorPrefix20220606\Symfony\Component\DependencyInjection\Loader\Configurator\service;
use RectorPrefix20220606\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;
use RectorPrefix20220606\Symplify\EasyParallel\ValueObject\EasyParallelConfig;
use RectorPrefix20220606\Symplify\PackageBuilder\Parameter\ParameterProvider;
use RectorPrefix20220606\Symplify\PackageBuilder\Php\TypeChecker;
use RectorPrefix20220606\Symplify\PackageBuilder\Reflection\PrivatesAccessor;
use RectorPrefix20220606\Symplify\PackageBuilder\Reflection\PrivatesCaller;
use RectorPrefix20220606\Symplify\PackageBuilder\Yaml\ParametersMerger;
use RectorPrefix20220606\Symplify\SmartFileSystem\FileSystemFilter;
use RectorPrefix20220606\Symplify\SmartFileSystem\FileSystemGuard;
use RectorPrefix20220606\Symplify\SmartFileSystem\Finder\FinderSanitizer;
use RectorPrefix20220606\Symplify\SmartFileSystem\Json\JsonFileSystem;
use RectorPrefix20220606\Symplify\SmartFileSystem\SmartFileSystem;
return static function (RectorConfig $rectorConfig) : void {
    // make use of https://github.com/symplify/easy-parallel
    $rectorConfig->import(EasyParallelConfig::FILE_PATH);
    $rectorConfig->paths([]);
    $rectorConfig->skip([]);
    $rectorConfig->autoloadPaths([]);
    $rectorConfig->bootstrapFiles([]);
    $rectorConfig->parallel(120, 16, 20);
    $rectorConfig->disableImportNames();
    $rectorConfig->importShortClasses();
    $rectorConfig->indent(' ', 4);
    $rectorConfig->fileExtensions(['php']);
    $rectorConfig->nestedChainMethodCallLimit(60);
    $rectorConfig->cacheDirectory(\sys_get_temp_dir() . '/rector_cached_files');
    $services = $rectorConfig->services();
    $services->defaults()->public()->autowire()->autoconfigure();
    $services->load('Rector\\', __DIR__ . '/../packages')->exclude([
        __DIR__ . '/../packages/Config/RectorConfig.php',
        __DIR__ . '/../packages/*/{ValueObject,Contract,Exception}',
        __DIR__ . '/../packages/BetterPhpDocParser/PhpDocInfo/PhpDocInfo.php',
        __DIR__ . '/../packages/Testing/PHPUnit',
        __DIR__ . '/../packages/BetterPhpDocParser/PhpDoc',
        __DIR__ . '/../packages/PHPStanStaticTypeMapper/Enum',
        __DIR__ . '/../packages/Caching/Cache.php',
        __DIR__ . '/../packages/NodeTypeResolver/PhpDocNodeVisitor/UnderscoreRenamePhpDocNodeVisitor.php',
        // used in PHPStan
        __DIR__ . '/../packages/NodeTypeResolver/Reflection/BetterReflection/RectorBetterReflectionSourceLocatorFactory.php',
        __DIR__ . '/../packages/NodeTypeResolver/Reflection/BetterReflection/SourceLocatorProvider/DynamicSourceLocatorProvider.php',
    ]);
    // psr-4
    $services->alias(PSR4AutoloadNamespaceMatcherInterface::class, PSR4NamespaceMatcher::class);
    $services->load('Rector\\', __DIR__ . '/../rules')->exclude([__DIR__ . '/../rules/*/ValueObject/*', __DIR__ . '/../rules/*/Rector/*', __DIR__ . '/../rules/*/Contract/*', __DIR__ . '/../rules/*/Exception/*', __DIR__ . '/../rules/*/Enum/*', __DIR__ . '/../rules/DowngradePhp80/Reflection/SimplePhpParameterReflection.php']);
    // parallel
    $services->set(ParametersMerger::class);
    // use faster in-memory cache in CI.
    // CI always starts from scratch, therefore IO intensive caching is not worth it
    $ciDetector = new CiDetector();
    if ($ciDetector->isCiDetected()) {
        $rectorConfig->cacheClass(MemoryCacheStorage::class);
    }
    $extensionConfigResolver = new ExtensionConfigResolver();
    $extensionConfigFiles = $extensionConfigResolver->provide();
    foreach ($extensionConfigFiles as $extensionConfigFile) {
        $rectorConfig->import($extensionConfigFile->getRealPath());
    }
    // require only in dev
    $rectorConfig->import(__DIR__ . '/../utils/compiler/config/config.php', null, 'not_found');
    $services->load('Rector\\Core\\', __DIR__ . '/../src')->exclude([__DIR__ . '/../src/Rector', __DIR__ . '/../src/Console/Style/RectorConsoleOutputStyle.php', __DIR__ . '/../src/Exception', __DIR__ . '/../src/DependencyInjection/CompilerPass', __DIR__ . '/../src/DependencyInjection/Loader', __DIR__ . '/../src/Kernel', __DIR__ . '/../src/ValueObject', __DIR__ . '/../src/Bootstrap', __DIR__ . '/../src/Enum', __DIR__ . '/../src/PhpParser/Node/CustomNode', __DIR__ . '/../src/PhpParser/ValueObject', __DIR__ . '/../src/functions', __DIR__ . '/../src/constants.php']);
    $services->alias(Application::class, ConsoleApplication::class);
    $services->set(EmptyConfigurableRectorCollector::class)->arg('$containerBuilder', service('service_container'));
    $services->set(SimpleCallableNodeTraverser::class);
    $services->set(BuilderFactory::class);
    $services->set(CloningVisitor::class);
    $services->set(NodeConnectingVisitor::class);
    $services->set(NodeFinder::class);
    $services->set(RectorConsoleOutputStyle::class)->factory([service(RectorConsoleOutputStyleFactory::class), 'create']);
    $services->set(Parser::class)->factory([service(PHPStanServicesFactory::class), 'createPHPStanParser']);
    $services->set(Lexer::class)->factory([service(PHPStanServicesFactory::class), 'createEmulativeLexer']);
    // symplify/package-builder
    $services->set(FileSystemGuard::class);
    $services->set(PrivatesAccessor::class);
    $services->set(PrivatesCaller::class);
    $services->set(FinderSanitizer::class);
    $services->set(FileSystemFilter::class);
    $services->set(ParameterProvider::class)->arg('$container', service('service_container'));
    $services->set(SmartFileSystem::class);
    $services->set(JsonFileSystem::class);
    $services->set(InflectorFactory::class);
    $services->set(Inflector::class)->factory([service(InflectorFactory::class), 'build']);
    $services->set(VersionParser::class);
    $services->set(TypeChecker::class);
    // phpdoc parser
    $services->set(\RectorPrefix20220606\PHPStan\PhpDocParser\Lexer\Lexer::class);
    $services->alias(PhpDocParser::class, BetterPhpDocParser::class);
    // cache
    $services->set(DependencyResolver::class)->factory([service(PHPStanServicesFactory::class), 'createDependencyResolver']);
    $services->set(FileHelper::class)->factory([service(PHPStanServicesFactory::class), 'createFileHelper']);
    $services->set(Cache::class)->factory([service(CacheFactory::class), 'create']);
    // type resolving
    $services->set(IntermediateSourceLocator::class);
    $services->alias(TypeParser::class, BetterTypeParser::class);
    // PHPStan services
    $services->set(ReflectionProvider::class)->factory([service(PHPStanServicesFactory::class), 'createReflectionProvider']);
    $services->set(NodeScopeResolver::class)->factory([service(PHPStanServicesFactory::class), 'createNodeScopeResolver']);
    $services->set(ScopeFactory::class)->factory([service(PHPStanServicesFactory::class), 'createScopeFactory']);
    $services->set(TypeNodeResolver::class)->factory([service(PHPStanServicesFactory::class), 'createTypeNodeResolver']);
    $services->set(DynamicSourceLocatorProvider::class)->factory([service(PHPStanServicesFactory::class), 'createDynamicSourceLocatorProvider']);
};
