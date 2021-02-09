<?php

use Rector\Renaming\Rector\PropertyFetch\RenamePropertyRector;
use Rector\Renaming\Tests\Rector\PropertyFetch\RenamePropertyRector\Source\ClassWithProperties;
use Rector\Renaming\ValueObject\RenameProperty;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(RenamePropertyRector::class)->call('configure', [[
        RenamePropertyRector::RENAMED_PROPERTIES => ValueObjectInliner::inline([

            new RenameProperty(ClassWithProperties::class, 'oldProperty', 'newProperty'),
            new RenameProperty(ClassWithProperties::class, 'anotherOldProperty', 'anotherNewProperty'),

        ]),
    ]]);
};
