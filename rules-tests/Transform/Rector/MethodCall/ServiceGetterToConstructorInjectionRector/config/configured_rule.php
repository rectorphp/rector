<?php

declare(strict_types=1);

use Rector\Tests\Transform\Rector\MethodCall\ServiceGetterToConstructorInjectionRector\Source\AnotherService;
use Rector\Tests\Transform\Rector\MethodCall\ServiceGetterToConstructorInjectionRector\Source\FirstService;
use Rector\Transform\Rector\MethodCall\ServiceGetterToConstructorInjectionRector;
use Rector\Transform\ValueObject\ServiceGetterToConstructorInjection;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(ServiceGetterToConstructorInjectionRector::class)
        ->configure([
            new ServiceGetterToConstructorInjection(FirstService::class, 'getAnotherService', AnotherService::class),
        ]);
};
