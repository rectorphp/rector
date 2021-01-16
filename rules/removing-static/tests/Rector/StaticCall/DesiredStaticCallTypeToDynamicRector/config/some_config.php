<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\RemovingStatic\Rector\StaticCall\DesiredStaticCallTypeToDynamicRector;
use Rector\RemovingStatic\Tests\Rector\StaticCall\DesiredStaticCallTypeToDynamicRector\Source\SomeStaticMethod;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::TYPES_TO_REMOVE_STATIC_FROM, [SomeStaticMethod::class]);

    $services = $containerConfigurator->services();
    $services->set(DesiredStaticCallTypeToDynamicRector::class);
};
