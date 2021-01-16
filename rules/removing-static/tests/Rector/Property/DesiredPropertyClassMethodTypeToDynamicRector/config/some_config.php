<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\RemovingStatic\Rector\Property\DesiredPropertyClassMethodTypeToDynamicRector;
use Rector\RemovingStatic\Tests\Rector\Property\DesiredPropertyClassMethodTypeToDynamicRector\Fixture\StaticProperty;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::TYPES_TO_REMOVE_STATIC_FROM, [StaticProperty::class]);

    $services = $containerConfigurator->services();
    $services->set(DesiredPropertyClassMethodTypeToDynamicRector::class);
};
