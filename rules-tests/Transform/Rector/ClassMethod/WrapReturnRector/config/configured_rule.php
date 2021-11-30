<?php

declare(strict_types=1);

use Rector\Tests\Transform\Rector\ClassMethod\WrapReturnRector\Source\SomeReturnClass;
use Rector\Transform\Rector\ClassMethod\WrapReturnRector;
use Rector\Transform\ValueObject\WrapReturn;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(WrapReturnRector::class)
        ->configure([new WrapReturn(SomeReturnClass::class, 'getItem', true)]);
};
