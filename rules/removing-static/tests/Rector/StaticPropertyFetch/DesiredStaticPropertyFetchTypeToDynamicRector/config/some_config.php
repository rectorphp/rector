<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\RemovingStatic\Rector\StaticPropertyFetch\DesiredStaticPropertyFetchTypeToDynamicRector;
use Rector\RemovingStatic\Tests\Rector\StaticPropertyFetch\DesiredStaticPropertyFetchTypeToDynamicRector\Source\SomeStaticType;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::TYPES_TO_REMOVE_STATIC_FROM, [SomeStaticType::class]);

    $services = $containerConfigurator->services();
    $services->set(DesiredStaticPropertyFetchTypeToDynamicRector::class);
};
