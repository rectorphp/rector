<?php

use Rector\CakePHP\Rector\MethodCall\ArrayToFluentCallRector;
use Rector\CakePHP\Tests\Rector\MethodCall\ArrayToFluentCallRector\Source\ConfigurableClass;
use Rector\CakePHP\Tests\Rector\MethodCall\ArrayToFluentCallRector\Source\FactoryClass;
use Rector\CakePHP\ValueObject\ArrayToFluentCall;
use Rector\CakePHP\ValueObject\FactoryMethod;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (
    \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator
): void {
    $services = $containerConfigurator->services();
    $services->set(ArrayToFluentCallRector::class)
        ->call('configure', [[
            ArrayToFluentCallRector::ARRAYS_TO_FLUENT_CALLS => ValueObjectInliner::inline([
                new ArrayToFluentCall(ConfigurableClass::class, [
                    'name' => 'setName',
                    'size' => 'setSize',
                ]),
            ]),
            ArrayToFluentCallRector::FACTORY_METHODS => ValueObjectInliner::inline([
                new FactoryMethod(FactoryClass::class, 'buildClass', ConfigurableClass::class, 2),
            ]),
        ]]);
};
