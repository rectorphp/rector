<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set(Option::ENABLE_CACHE, false);
    $parameters->set(Option::CACHE_DIR, sys_get_temp_dir() . '/rector_cached_files');
    $parameters->set(Option::PHPSTAN_FOR_RECTOR_PATH, getcwd() . '/phpstan-for-rector.neon');
};
