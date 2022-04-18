<?php

declare (strict_types=1);
namespace RectorPrefix20220418;

use RectorPrefix20220418\OndraM\CiDetector\CiDetector;
use Rector\Caching\ValueObject\Storage\MemoryCacheStorage;
use Rector\Config\RectorConfig;
use Rector\Core\Configuration\Option;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    // paths and extensions
    $rectorConfig->paths([]);
    $parameters = $rectorConfig->parameters();
    $parameters->set(\Rector\Core\Configuration\Option::FILE_EXTENSIONS, ['php']);
    $rectorConfig->autoloadPaths([]);
    // these files will be executed, useful e.g. for constant definitions
    $rectorConfig->bootstrapFiles([]);
    // parallel
    $rectorConfig->disableParallel();
    $parameters->set(\Rector\Core\Configuration\Option::PARALLEL_MAX_NUMBER_OF_PROCESSES, 16);
    $parameters->set(\Rector\Core\Configuration\Option::PARALLEL_JOB_SIZE, 20);
    $parameters->set(\Rector\Core\Configuration\Option::PARALLEL_TIMEOUT_IN_SECONDS, 120);
    // FQN class importing
    $rectorConfig->disableImportNames();
    $parameters->set(\Rector\Core\Configuration\Option::IMPORT_SHORT_CLASSES, \true);
    $parameters->set(\Rector\Core\Configuration\Option::NESTED_CHAIN_METHOD_CALL_LIMIT, 60);
    $rectorConfig->skip([]);
    $parameters->set(\Rector\Core\Configuration\Option::PHPSTAN_FOR_RECTOR_PATH, null);
    // cache
    $parameters->set(\Rector\Core\Configuration\Option::CACHE_DIR, \sys_get_temp_dir() . '/rector_cached_files');
    // use faster in-memory cache in CI.
    // CI always starts from scratch, therefore IO intensive caching is not worth it
    $ciDetector = new \RectorPrefix20220418\OndraM\CiDetector\CiDetector();
    if ($ciDetector->isCiDetected()) {
        $parameters->set(\Rector\Core\Configuration\Option::CACHE_CLASS, \Rector\Caching\ValueObject\Storage\MemoryCacheStorage::class);
    }
};
