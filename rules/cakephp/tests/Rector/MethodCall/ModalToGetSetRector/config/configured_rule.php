<?php

return static function (
    \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator
): void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\CakePHP\Rector\MethodCall\ModalToGetSetRector::class)->call('configure', [[
        \Rector\CakePHP\Rector\MethodCall\ModalToGetSetRector::UNPREFIXED_METHODS_TO_GET_SET => \Symplify\SymfonyPhpConfig\ValueObjectInliner::inline([
            

            new \Rector\CakePHP\ValueObject\ModalToGetSet(
                \Rector\CakePHP\Tests\Rector\MethodCall\ModalToGetSetRector\Source\SomeModelType::class,
                'config',
                null,
                null,
                2,
                'array'
            ),
            new \Rector\CakePHP\ValueObject\ModalToGetSet(
                \Rector\CakePHP\Tests\Rector\MethodCall\ModalToGetSetRector\Source\SomeModelType::class,
                'customMethod',
                'customMethodGetName',
                'customMethodSetName',
                2,
                'array'
            ),
            new \Rector\CakePHP\ValueObject\ModalToGetSet(
                \Rector\CakePHP\Tests\Rector\MethodCall\ModalToGetSetRector\Source\SomeModelType::class,
                'makeEntity',
                'createEntity',
                'generateEntity'
            ),
            new \Rector\CakePHP\ValueObject\ModalToGetSet(
                \Rector\CakePHP\Tests\Rector\MethodCall\ModalToGetSetRector\Source\SomeModelType::class,
                'method'
            ),

            
        ]),
    ]]);
};
