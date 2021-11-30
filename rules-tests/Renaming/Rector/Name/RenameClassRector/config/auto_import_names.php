<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Tests\Renaming\Rector\Name\RenameClassRector\Source\FirstNamespace\SomeServiceClass as SomeServiceClassFirstNamespace;
use Rector\Tests\Renaming\Rector\Name\RenameClassRector\Source\NewClass;
use Rector\Tests\Renaming\Rector\Name\RenameClassRector\Source\OldClass;
use Rector\Tests\Renaming\Rector\Name\RenameClassRector\Source\SecondNamespace\SomeServiceClass;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::AUTO_IMPORT_NAMES, true);

    $services = $containerConfigurator->services();
    $services->set(RenameClassRector::class)
        ->configure([
            OldClass::class => NewClass::class,
            SomeServiceClassFirstNamespace::class => SomeServiceClass::class,
        ]);
};
