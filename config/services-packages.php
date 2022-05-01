<?php

declare (strict_types=1);
namespace RectorPrefix20220501;

use Rector\Config\RectorConfig;
use RectorPrefix20220501\Symplify\EasyParallel\ValueObject\EasyParallelConfig;
use RectorPrefix20220501\Symplify\PackageBuilder\Yaml\ParametersMerger;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    // make use of https://github.com/symplify/easy-parallel
    $rectorConfig->import(\RectorPrefix20220501\Symplify\EasyParallel\ValueObject\EasyParallelConfig::FILE_PATH);
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
    $services->set(\RectorPrefix20220501\Symplify\PackageBuilder\Yaml\ParametersMerger::class);
};
