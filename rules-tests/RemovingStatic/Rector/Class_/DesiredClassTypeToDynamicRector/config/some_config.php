<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\RemovingStatic\Rector\Class_\DesiredClassTypeToDynamicRector;
use Rector\RemovingStatic\Tests\Rector\Class_\DesiredClassTypeToDynamicRector\Source\FirstStaticClass;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::TYPES_TO_REMOVE_STATIC_FROM, [FirstStaticClass::class]);

    $services = $containerConfigurator->services();
    $services->set(DesiredClassTypeToDynamicRector::class);
};
