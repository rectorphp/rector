<?php

declare (strict_types=1);
namespace RectorPrefix202308;

use RectorPrefix202308\OndraM\CiDetector\CiDetector;
use Rector\Caching\ValueObject\Storage\MemoryCacheStorage;
use Rector\Config\RectorConfig;
use Rector\Core\Bootstrap\ExtensionConfigResolver;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->paths([]);
    $rectorConfig->skip([]);
    $rectorConfig->autoloadPaths([]);
    $rectorConfig->bootstrapFiles([]);
    $rectorConfig->parallel(120, 16, 20);
    // to avoid autoimporting out of the box
    $rectorConfig->importNames(\false, \false);
    $rectorConfig->removeUnusedImports(\false);
    $rectorConfig->importShortClasses();
    $rectorConfig->indent(' ', 4);
    $rectorConfig->fileExtensions(['php']);
    $rectorConfig->cacheDirectory(\sys_get_temp_dir() . '/rector_cached_files');
    $rectorConfig->containerCacheDirectory(\sys_get_temp_dir());
    // use faster in-memory cache in CI.
    // CI always starts from scratch, therefore IO intensive caching is not worth it
    if ((new CiDetector())->isCiDetected()) {
        $rectorConfig->cacheClass(MemoryCacheStorage::class);
    }
    $extensionConfigResolver = new ExtensionConfigResolver();
    $extensionConfigFiles = $extensionConfigResolver->provide();
    foreach ($extensionConfigFiles as $extensionConfigFile) {
        $rectorConfig->import($extensionConfigFile);
    }
};
