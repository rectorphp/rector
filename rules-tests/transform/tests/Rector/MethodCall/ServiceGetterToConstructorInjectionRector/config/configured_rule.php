<?php

declare(strict_types=1);

use Rector\Transform\Rector\MethodCall\ServiceGetterToConstructorInjectionRector;
use Rector\Transform\Tests\Rector\MethodCall\ServiceGetterToConstructorInjectionRector\Source\AnotherService;
use Rector\Transform\Tests\Rector\MethodCall\ServiceGetterToConstructorInjectionRector\Source\FirstService;
use Rector\Transform\ValueObject\ServiceGetterToConstructorInjection;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ServiceGetterToConstructorInjectionRector::class)
        ->call('configure', [[
            ServiceGetterToConstructorInjectionRector::METHOD_CALL_TO_SERVICES => ValueObjectInliner::inline([
                new ServiceGetterToConstructorInjection(
                    FirstService::class,
                    'getAnotherService',
                    AnotherService::class
                ),
            ]),
        ]]
    );
};
