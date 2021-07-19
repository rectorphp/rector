<?php

declare(strict_types=1);

use Rector\CodingStyle\Rector\ClassMethod\OrderAttributesRector;
use Rector\Tests\CodingStyle\Rector\ClassMethod\OrderAttributesRector\Source\FirstAttribute;
use Rector\Tests\CodingStyle\Rector\ClassMethod\OrderAttributesRector\Source\SecondAttribute;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(OrderAttributesRector::class)
        ->call('configure', [[
            OrderAttributesRector::ATTRIBUTES_ORDER => [FirstAttribute::class, SecondAttribute::class],
        ]]);
};
