<?php

use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\Tests\Rector\Name\RenameClassRector\Source\NewClass;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenameClassRector::class)
        ->call('configure', [[
            RenameClassRector::OLD_TO_NEW_CLASSES => [
                \Rector\Renaming\Tests\Rector\Name\RenameClassRector\Source\OldClass::class => NewClass::class,
                // Laravel
                'Session' => 'Illuminate\Support\Facades\Session',
                'Form' => 'Collective\Html\FormFacade',
                'Html' => 'Collective\Html\HtmlFacade',
            ],
        ]]);
};
