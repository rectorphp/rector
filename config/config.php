<?php

declare (strict_types=1);
namespace RectorPrefix20220527;

use RectorPrefix20220527\OndraM\CiDetector\CiDetector;
use Rector\Caching\ValueObject\Storage\MemoryCacheStorage;
use Rector\Config\RectorConfig;
use Rector\Core\Bootstrap\ExtensionConfigResolver;
use RectorPrefix20220527\Symplify\EasyParallel\ValueObject\EasyParallelConfig;
use RectorPrefix20220527\Symplify\PackageBuilder\Yaml\ParametersMerger;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/services.php');
    $rectorConfig->import(__DIR__ . '/services-rules.php');
    // make use of https://github.com/symplify/easy-parallel
    $rectorConfig->import(\RectorPrefix20220527\Symplify\EasyParallel\ValueObject\EasyParallelConfig::FILE_PATH);
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
    // parallel
    $services->set(\RectorPrefix20220527\Symplify\PackageBuilder\Yaml\ParametersMerger::class);
    // use faster in-memory cache in CI.
    // CI always starts from scratch, therefore IO intensive caching is not worth it
    $ciDetector = new \RectorPrefix20220527\OndraM\CiDetector\CiDetector();
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
};
