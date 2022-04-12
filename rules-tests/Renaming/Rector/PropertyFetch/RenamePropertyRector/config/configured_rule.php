<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\PropertyFetch\RenamePropertyRector;
use Rector\Renaming\ValueObject\RenameProperty;
use Rector\Tests\Renaming\Rector\PropertyFetch\RenamePropertyRector\Source\ClassWithProperties;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(RenamePropertyRector::class)
        ->configure([

            new RenameProperty(ClassWithProperties::class, 'oldProperty', 'newProperty'),
            new RenameProperty(ClassWithProperties::class, 'anotherOldProperty', 'anotherNewProperty'),

            new RenameProperty(
                'Rector\Tests\Renaming\Rector\PropertyFetch\RenamePropertyRector\Fixture\ClassWithOldProperty',
                'oldProperty',
                'newProperty'
            ),
            new RenameProperty(
                'Rector\Tests\Renaming\Rector\PropertyFetch\RenamePropertyRector\Fixture\ClassWithOldProperty2',
                'oldProperty',
                'newProperty'
            ),
            new RenameProperty(
                'Rector\Tests\Renaming\Rector\PropertyFetch\RenamePropertyRector\Fixture\DoNotChangeToPropertyExists',
                'oldProperty',
                'newProperty'
            ),
            new RenameProperty(
                'Rector\Tests\Renaming\Rector\PropertyFetch\RenamePropertyRector\Source\ParentClassWithOldProperty',
                'oldProperty',
                'newProperty'
            ),
        ]);
};
