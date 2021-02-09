<?php

return static function (
    \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator
): void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\Renaming\Rector\PropertyFetch\RenamePropertyRector::class)->call('configure', [[
        \Rector\Renaming\Rector\PropertyFetch\RenamePropertyRector::RENAMED_PROPERTIES => \Symplify\SymfonyPhpConfig\ValueObjectInliner::inline([
            

















            new \Rector\Renaming\ValueObject\RenameProperty(
                \Rector\Renaming\Tests\Rector\PropertyFetch\RenamePropertyRector\Source\ClassWithProperties::class,
                'oldProperty',
                'newProperty'
            ),
            new \Rector\Renaming\ValueObject\RenameProperty(
                \Rector\Renaming\Tests\Rector\PropertyFetch\RenamePropertyRector\Source\ClassWithProperties::class,
                'anotherOldProperty',
                'anotherNewProperty'
            ),
























            
        ]),
    ]]);
};
