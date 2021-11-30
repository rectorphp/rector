<?php

declare(strict_types=1);

use Rector\Tests\Transform\Rector\New_\NewToConstructorInjectionRector\Source\DummyValidator;
use Rector\Transform\Rector\New_\NewToConstructorInjectionRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(NewToConstructorInjectionRector::class)
        ->configure([DummyValidator::class]);
};
