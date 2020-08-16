<?php

declare(strict_types=1);

use Rector\Renaming\Rector\Name\RenameClassRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

# see https://stackoverflow.com/a/43495506/1348344

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenameClassRector::class)
        ->call('configure', [[
            RenameClassRector::OLD_TO_NEW_CLASSES => [
                'Twig_Function_Node' => 'Twig_SimpleFunction',
                'Twig_Function' => 'Twig_SimpleFunction',
                'Twig_Filter' => 'Twig_SimpleFilter',
                'Twig_Test' => 'Twig_SimpleTest',
            ],
        ]]);
};
