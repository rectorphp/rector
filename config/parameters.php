<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\ProjectType;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set(Option::PHPSTAN_FOR_RECTOR_PATH, getcwd() . '/phpstan-for-rector.neon');

    // paths and extensions
    $parameters->set(Option::PATHS, []);
    $parameters->set(Option::FILE_EXTENSIONS, ['php']);
    $parameters->set(Option::AUTOLOAD_PATHS, []);

    // FQN class importing
    $parameters->set(Option::AUTO_IMPORT_NAMES, false);
    $parameters->set(Option::IMPORT_SHORT_CLASSES, true);
    $parameters->set(Option::IMPORT_DOC_BLOCKS, true);

    $parameters->set(Option::PHP_VERSION_FEATURES, null);
    $parameters->set(Option::PROJECT_TYPE, ProjectType::PROPRIETARY);
    $parameters->set(Option::NESTED_CHAIN_METHOD_CALL_LIMIT, 30);
    $parameters->set(Option::SKIP, []);

    // cache
    $parameters->set(Option::ENABLE_CACHE, false);
    $parameters->set(Option::CACHE_DIR, sys_get_temp_dir() . '/rector_cached_files');
};
