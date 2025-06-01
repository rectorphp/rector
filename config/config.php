<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use RectorPrefix202506\OndraM\CiDetector\CiDetector;
use Rector\Bootstrap\ExtensionConfigResolver;
use Rector\Caching\ValueObject\Storage\MemoryCacheStorage;
use Rector\Config\RectorConfig;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->paths([]);
    $rectorConfig->skip([]);
    $rectorConfig->autoloadPaths([]);
    $rectorConfig->bootstrapFiles([]);
    $rectorConfig->parallel();
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
    // load internal rector-* extension configs
    $extensionConfigResolver = new ExtensionConfigResolver();
    foreach ($extensionConfigResolver->provide() as $extensionConfigFile) {
        $rectorConfig->import($extensionConfigFile);
    }
    // use original php-parser printer to avoid BC break on fluent call
    $rectorConfig->newLineOnFluentCall(\false);
    // allow real paths in output formatters
    $rectorConfig->reportingRealPath(\false);
};
