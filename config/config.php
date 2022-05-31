<?php

declare (strict_types=1);
namespace RectorPrefix20220531;

use RectorPrefix20220531\Composer\Semver\VersionParser;
use RectorPrefix20220531\Doctrine\Inflector\Inflector;
use RectorPrefix20220531\Doctrine\Inflector\Rules\English\InflectorFactory;
use RectorPrefix20220531\OndraM\CiDetector\CiDetector;
use PhpParser\BuilderFactory;
use PhpParser\Lexer;
use PhpParser\NodeFinder;
use PhpParser\NodeVisitor\CloningVisitor;
use PhpParser\NodeVisitor\NodeConnectingVisitor;
use PHPStan\Analyser\NodeScopeResolver;
use PHPStan\Analyser\ScopeFactory;
use PHPStan\Dependency\DependencyResolver;
use PHPStan\File\FileHelper;
use PHPStan\Parser\Parser;
use PHPStan\PhpDoc\TypeNodeResolver;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TypeParser;
use PHPStan\Reflection\ReflectionProvider;
use Rector\BetterPhpDocParser\PhpDocParser\BetterPhpDocParser;
use Rector\BetterPhpDocParser\PhpDocParser\BetterTypeParser;
use Rector\Caching\Cache;
use Rector\Caching\CacheFactory;
use Rector\Caching\ValueObject\Storage\MemoryCacheStorage;
use Rector\Config\RectorConfig;
use Rector\Core\Bootstrap\ExtensionConfigResolver;
use Rector\Core\Console\ConsoleApplication;
use Rector\Core\Console\Style\RectorConsoleOutputStyle;
use Rector\Core\Console\Style\RectorConsoleOutputStyleFactory;
use Rector\Core\Validation\Collector\EmptyConfigurableRectorCollector;
use Rector\NodeTypeResolver\DependencyInjection\PHPStanServicesFactory;
use Rector\NodeTypeResolver\Reflection\BetterReflection\SourceLocator\IntermediateSourceLocator;
use Rector\NodeTypeResolver\Reflection\BetterReflection\SourceLocatorProvider\DynamicSourceLocatorProvider;
use Rector\PSR4\Composer\PSR4NamespaceMatcher;
use Rector\PSR4\Contract\PSR4AutoloadNamespaceMatcherInterface;
use RectorPrefix20220531\Symfony\Component\Console\Application;
use function RectorPrefix20220531\Symfony\Component\DependencyInjection\Loader\Configurator\service;
use RectorPrefix20220531\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;
use RectorPrefix20220531\Symplify\EasyParallel\ValueObject\EasyParallelConfig;
use RectorPrefix20220531\Symplify\PackageBuilder\Parameter\ParameterProvider;
use RectorPrefix20220531\Symplify\PackageBuilder\Php\TypeChecker;
use RectorPrefix20220531\Symplify\PackageBuilder\Reflection\PrivatesAccessor;
use RectorPrefix20220531\Symplify\PackageBuilder\Reflection\PrivatesCaller;
use RectorPrefix20220531\Symplify\PackageBuilder\Yaml\ParametersMerger;
use RectorPrefix20220531\Symplify\SmartFileSystem\FileSystemFilter;
use RectorPrefix20220531\Symplify\SmartFileSystem\FileSystemGuard;
use RectorPrefix20220531\Symplify\SmartFileSystem\Finder\FinderSanitizer;
use RectorPrefix20220531\Symplify\SmartFileSystem\Json\JsonFileSystem;
use RectorPrefix20220531\Symplify\SmartFileSystem\SmartFileSystem;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    // make use of https://github.com/symplify/easy-parallel
    $rectorConfig->import(\RectorPrefix20220531\Symplify\EasyParallel\ValueObject\EasyParallelConfig::FILE_PATH);
    $rectorConfig->paths([]);
    $rectorConfig->skip([]);
    $rectorConfig->autoloadPaths([]);
    $rectorConfig->bootstrapFiles([]);
    $rectorConfig->parallel(120, 16, 20);
    $rectorConfig->disableImportNames();
    $rectorConfig->importShortClasses();
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
        // used in PHPStan
        __DIR__ . '/../packages/NodeTypeResolver/Reflection/BetterReflection/RectorBetterReflectionSourceLocatorFactory.php',
        __DIR__ . '/../packages/NodeTypeResolver/Reflection/BetterReflection/SourceLocatorProvider/DynamicSourceLocatorProvider.php',
    ]);
    // psr-4
    $services->alias(\Rector\PSR4\Contract\PSR4AutoloadNamespaceMatcherInterface::class, \Rector\PSR4\Composer\PSR4NamespaceMatcher::class);
    $services->load('Rector\\', __DIR__ . '/../rules')->exclude([__DIR__ . '/../rules/*/ValueObject/*', __DIR__ . '/../rules/*/Rector/*', __DIR__ . '/../rules/*/Contract/*', __DIR__ . '/../rules/*/Exception/*', __DIR__ . '/../rules/*/Enum/*', __DIR__ . '/../rules/DowngradePhp80/Reflection/SimplePhpParameterReflection.php']);
    // parallel
    $services->set(\RectorPrefix20220531\Symplify\PackageBuilder\Yaml\ParametersMerger::class);
    // use faster in-memory cache in CI.
    // CI always starts from scratch, therefore IO intensive caching is not worth it
    $ciDetector = new \RectorPrefix20220531\OndraM\CiDetector\CiDetector();
    if ($ciDetector->isCiDetected()) {
        $rectorConfig->cacheClass(\Rector\Caching\ValueObject\Storage\MemoryCacheStorage::class);
    }
    $extensionConfigResolver = new \Rector\Core\Bootstrap\ExtensionConfigResolver();
    $extensionConfigFiles = $extensionConfigResolver->provide();
    foreach ($extensionConfigFiles as $extensionConfigFile) {
        $rectorConfig->import($extensionConfigFile->getRealPath());
    }
    // require only in dev
    $rectorConfig->import(__DIR__ . '/../utils/compiler/config/config.php', null, 'not_found');
    $services->load('Rector\\Core\\', __DIR__ . '/../src')->exclude([__DIR__ . '/../src/Rector', __DIR__ . '/../src/Console/Style/RectorConsoleOutputStyle.php', __DIR__ . '/../src/Exception', __DIR__ . '/../src/DependencyInjection/CompilerPass', __DIR__ . '/../src/DependencyInjection/Loader', __DIR__ . '/../src/Kernel', __DIR__ . '/../src/ValueObject', __DIR__ . '/../src/Bootstrap', __DIR__ . '/../src/Enum', __DIR__ . '/../src/PhpParser/Node/CustomNode', __DIR__ . '/../src/PhpParser/ValueObject', __DIR__ . '/../src/functions', __DIR__ . '/../src/constants.php']);
    $services->alias(\RectorPrefix20220531\Symfony\Component\Console\Application::class, \Rector\Core\Console\ConsoleApplication::class);
    $services->set(\Rector\Core\Validation\Collector\EmptyConfigurableRectorCollector::class)->arg('$containerBuilder', \RectorPrefix20220531\Symfony\Component\DependencyInjection\Loader\Configurator\service('service_container'));
    $services->set(\RectorPrefix20220531\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser::class);
    $services->set(\PhpParser\BuilderFactory::class);
    $services->set(\PhpParser\NodeVisitor\CloningVisitor::class);
    $services->set(\PhpParser\NodeVisitor\NodeConnectingVisitor::class);
    $services->set(\PhpParser\NodeFinder::class);
    $services->set(\Rector\Core\Console\Style\RectorConsoleOutputStyle::class)->factory([\RectorPrefix20220531\Symfony\Component\DependencyInjection\Loader\Configurator\service(\Rector\Core\Console\Style\RectorConsoleOutputStyleFactory::class), 'create']);
    $services->set(\PHPStan\Parser\Parser::class)->factory([\RectorPrefix20220531\Symfony\Component\DependencyInjection\Loader\Configurator\service(\Rector\NodeTypeResolver\DependencyInjection\PHPStanServicesFactory::class), 'createPHPStanParser']);
    $services->set(\PhpParser\Lexer::class)->factory([\RectorPrefix20220531\Symfony\Component\DependencyInjection\Loader\Configurator\service(\Rector\NodeTypeResolver\DependencyInjection\PHPStanServicesFactory::class), 'createEmulativeLexer']);
    // symplify/package-builder
    $services->set(\RectorPrefix20220531\Symplify\SmartFileSystem\FileSystemGuard::class);
    $services->set(\RectorPrefix20220531\Symplify\PackageBuilder\Reflection\PrivatesAccessor::class);
    $services->set(\RectorPrefix20220531\Symplify\PackageBuilder\Reflection\PrivatesCaller::class);
    $services->set(\RectorPrefix20220531\Symplify\SmartFileSystem\Finder\FinderSanitizer::class);
    $services->set(\RectorPrefix20220531\Symplify\SmartFileSystem\FileSystemFilter::class);
    $services->set(\RectorPrefix20220531\Symplify\PackageBuilder\Parameter\ParameterProvider::class)->arg('$container', \RectorPrefix20220531\Symfony\Component\DependencyInjection\Loader\Configurator\service('service_container'));
    $services->set(\RectorPrefix20220531\Symplify\SmartFileSystem\SmartFileSystem::class);
    $services->set(\RectorPrefix20220531\Symplify\SmartFileSystem\Json\JsonFileSystem::class);
    $services->set(\RectorPrefix20220531\Doctrine\Inflector\Rules\English\InflectorFactory::class);
    $services->set(\RectorPrefix20220531\Doctrine\Inflector\Inflector::class)->factory([\RectorPrefix20220531\Symfony\Component\DependencyInjection\Loader\Configurator\service(\RectorPrefix20220531\Doctrine\Inflector\Rules\English\InflectorFactory::class), 'build']);
    $services->set(\RectorPrefix20220531\Composer\Semver\VersionParser::class);
    $services->set(\RectorPrefix20220531\Symplify\PackageBuilder\Php\TypeChecker::class);
    // phpdoc parser
    $services->set(\PHPStan\PhpDocParser\Lexer\Lexer::class);
    $services->alias(\PHPStan\PhpDocParser\Parser\PhpDocParser::class, \Rector\BetterPhpDocParser\PhpDocParser\BetterPhpDocParser::class);
    // cache
    $services->set(\PHPStan\Dependency\DependencyResolver::class)->factory([\RectorPrefix20220531\Symfony\Component\DependencyInjection\Loader\Configurator\service(\Rector\NodeTypeResolver\DependencyInjection\PHPStanServicesFactory::class), 'createDependencyResolver']);
    $services->set(\PHPStan\File\FileHelper::class)->factory([\RectorPrefix20220531\Symfony\Component\DependencyInjection\Loader\Configurator\service(\Rector\NodeTypeResolver\DependencyInjection\PHPStanServicesFactory::class), 'createFileHelper']);
    $services->set(\Rector\Caching\Cache::class)->factory([\RectorPrefix20220531\Symfony\Component\DependencyInjection\Loader\Configurator\service(\Rector\Caching\CacheFactory::class), 'create']);
    // type resolving
    $services->set(\Rector\NodeTypeResolver\Reflection\BetterReflection\SourceLocator\IntermediateSourceLocator::class);
    $services->alias(\PHPStan\PhpDocParser\Parser\TypeParser::class, \Rector\BetterPhpDocParser\PhpDocParser\BetterTypeParser::class);
    // PHPStan services
    $services->set(\PHPStan\Reflection\ReflectionProvider::class)->factory([\RectorPrefix20220531\Symfony\Component\DependencyInjection\Loader\Configurator\service(\Rector\NodeTypeResolver\DependencyInjection\PHPStanServicesFactory::class), 'createReflectionProvider']);
    $services->set(\PHPStan\Analyser\NodeScopeResolver::class)->factory([\RectorPrefix20220531\Symfony\Component\DependencyInjection\Loader\Configurator\service(\Rector\NodeTypeResolver\DependencyInjection\PHPStanServicesFactory::class), 'createNodeScopeResolver']);
    $services->set(\PHPStan\Analyser\ScopeFactory::class)->factory([\RectorPrefix20220531\Symfony\Component\DependencyInjection\Loader\Configurator\service(\Rector\NodeTypeResolver\DependencyInjection\PHPStanServicesFactory::class), 'createScopeFactory']);
    $services->set(\PHPStan\PhpDoc\TypeNodeResolver::class)->factory([\RectorPrefix20220531\Symfony\Component\DependencyInjection\Loader\Configurator\service(\Rector\NodeTypeResolver\DependencyInjection\PHPStanServicesFactory::class), 'createTypeNodeResolver']);
    $services->set(\Rector\NodeTypeResolver\Reflection\BetterReflection\SourceLocatorProvider\DynamicSourceLocatorProvider::class)->factory([\RectorPrefix20220531\Symfony\Component\DependencyInjection\Loader\Configurator\service(\Rector\NodeTypeResolver\DependencyInjection\PHPStanServicesFactory::class), 'createDynamicSourceLocatorProvider']);
};
