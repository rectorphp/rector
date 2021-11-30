<?php

declare(strict_types=1);

use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->import(PHPUnitSetList::PHPUNIT_60);

    $services = $containerConfigurator->services();
    $services->set(RenameClassRector::class)
        ->configure([
            RenameClassRector::OLD_TO_NEW_CLASSES => [
                'Old' => 'New',
            ],
        ]);
};
