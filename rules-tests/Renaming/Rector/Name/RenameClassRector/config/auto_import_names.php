<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\PostRector\Rector\NameImportingPostRector;
use Rector\PostRector\Rector\NodeAddingPostRector;
use Rector\PostRector\Rector\UseAddingPostRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Tests\Renaming\Rector\Name\RenameClassRector\Source\FirstNamespace\SomeServiceClass as SomeServiceClassFirstNamespace;
use Rector\Tests\Renaming\Rector\Name\RenameClassRector\Source\NewClass;
use Rector\Tests\Renaming\Rector\Name\RenameClassRector\Source\OldClass;
use Rector\Tests\Renaming\Rector\Name\RenameClassRector\Source\SecondNamespace\SomeServiceClass;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::AUTO_IMPORT_NAMES, true);

//    $parameters->set(Option::SKIP, [
//        NameImportingPostRector::class,
//        UseAddingPostRector::class,
//        NodeAddingPostRector::class,
//    ]);

    $services = $containerConfigurator->services();

    $services->set(RenameClassRector::class)
        ->call('configure', [[
            RenameClassRector::OLD_TO_NEW_CLASSES => [
                OldClass::class => NewClass::class,
                SomeServiceClassFirstNamespace::class => SomeServiceClass::class,
            ],
        ]]);
};
