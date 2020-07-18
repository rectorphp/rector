<?php

declare(strict_types=1);

use Rector\Renaming\Rector\Class_\RenameClassRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenameClassRector::class)
        ->arg('$oldToNewClasses', [
            # see https://stackoverflow.com/a/43495506/1348344
            'Twig_Function_Node' => 'Twig_SimpleFunction',
            'Twig_Function' => 'Twig_SimpleFunction',
            'Twig_Filter' => 'Twig_SimpleFilter',
            'Twig_Test' => 'Twig_SimpleTest',
        ]);
};
