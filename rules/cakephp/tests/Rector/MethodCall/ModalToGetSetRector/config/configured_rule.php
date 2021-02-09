<?php

use Rector\CakePHP\Rector\MethodCall\ModalToGetSetRector;
use Rector\CakePHP\Tests\Rector\MethodCall\ModalToGetSetRector\Source\SomeModelType;
use Rector\CakePHP\ValueObject\ModalToGetSet;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(ModalToGetSetRector::class)->call('configure', [[
        ModalToGetSetRector::UNPREFIXED_METHODS_TO_GET_SET => ValueObjectInliner::inline([

            new ModalToGetSet(SomeModelType::class, 'config', null, null, 2, 'array'),
            new ModalToGetSet(
                SomeModelType::class,
                'customMethod',
                'customMethodGetName',
                'customMethodSetName',
                2,
                'array'
            ),
            new ModalToGetSet(SomeModelType::class, 'makeEntity', 'createEntity', 'generateEntity'),
            new ModalToGetSet(SomeModelType::class, 'method'),

        ]),
    ]]);
};
