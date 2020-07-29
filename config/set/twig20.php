<?php

declare(strict_types=1);

use Rector\Renaming\Rector\Class_\RenameClassRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenameClassRector::class)
        ->call('configure', [[
            '$oldToNewClasses' => [
                #filters
                # see https://twig.symfony.com/doc/1.x/deprecated.html
                'Twig_SimpleFilter' => 'Twig_Filter',
                #functions
                # see https://twig.symfony.com/doc/1.x/deprecated.html
                'Twig_SimpleFunction' => 'Twig_Function',
                # see https://github.com/bolt/bolt/pull/6596
                'Twig_SimpleTest' => 'Twig_Test',
            ],
        ]]);
};
