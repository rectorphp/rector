<?php

declare(strict_types=1);

use Rector\Transform\Rector\String_\ToStringToMethodCallRector;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(ToStringToMethodCallRector::class)
        ->configure([
            ConfigCache::class => 'getPath',
        ]);
};
