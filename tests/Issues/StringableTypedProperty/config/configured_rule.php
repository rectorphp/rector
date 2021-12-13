<?php

declare(strict_types=1);

use Rector\Php74\Rector\Property\TypedPropertyRector;
use Rector\Php80\Rector\Class_\StringableForToStringRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(StringableForToStringRector::class);
    $services->set(TypedPropertyRector::class);
};
