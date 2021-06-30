<?php

declare (strict_types=1);
namespace RectorPrefix20210630;

use Rector\Core\Configuration\Option;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $parameters = $containerConfigurator->parameters();
    // paths and extensions
    $parameters->set(\Rector\Core\Configuration\Option::PATHS, []);
    $parameters->set(\Rector\Core\Configuration\Option::FILE_EXTENSIONS, ['php']);
    $parameters->set(\Rector\Core\Configuration\Option::AUTOLOAD_PATHS, []);
    // these files will be executed, useful e.g. for constant definitions
    $parameters->set(\Rector\Core\Configuration\Option::BOOTSTRAP_FILES, []);
    // FQN class importing
    $parameters->set(\Rector\Core\Configuration\Option::AUTO_IMPORT_NAMES, \false);
    $parameters->set(\Rector\Core\Configuration\Option::IMPORT_SHORT_CLASSES, \true);
    $parameters->set(\Rector\Core\Configuration\Option::IMPORT_DOC_BLOCKS, \true);
    $parameters->set(\Rector\Core\Configuration\Option::PHP_VERSION_FEATURES, null);
    $parameters->set(\Rector\Core\Configuration\Option::NESTED_CHAIN_METHOD_CALL_LIMIT, 30);
    $parameters->set(\Rector\Core\Configuration\Option::SKIP, []);
    $parameters->set(\Rector\Core\Configuration\Option::PHPSTAN_FOR_RECTOR_PATH, null);
    // cache
    $parameters->set(\Rector\Core\Configuration\Option::CACHE_DIR, \sys_get_temp_dir() . '/rector_cached_files');
};
