<?php

declare(strict_types=1);

use Rector\Core\NonPhpFile\Rector\RenameClassNonPhpRector;
use Rector\Tests\Renaming\Rector\Name\RenameClassRector\Source\NewClass;
use Rector\Tests\Renaming\Rector\Name\RenameClassRector\Source\OldClass;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenameClassNonPhpRector::class)
        ->call('configure', [[
            RenameClassNonPhpRector::RENAME_CLASSES => [
                'Session' => 'Illuminate\Support\Facades\Session',
                OldClass::class => NewClass::class,
                // Laravel
                'Form' => 'Collective\Html\FormFacade',
                'Html' => 'Collective\Html\HtmlFacade',
            ],
        ]]);
};
