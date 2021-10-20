<?php

declare (strict_types=1);
namespace RectorPrefix20211020;

use Rector\Renaming\Rector\Name\RenameClassRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\Renaming\Rector\Name\RenameClassRector::class)->call('configure', [[\Rector\Renaming\Rector\Name\RenameClassRector::OLD_TO_NEW_CLASSES => [
        #filters
        # see https://twig.symfony.com/doc/1.x/deprecated.html
        'Twig_SimpleFilter' => 'Twig_Filter',
        #functions
        # see https://twig.symfony.com/doc/1.x/deprecated.html
        'Twig_SimpleFunction' => 'Twig_Function',
        # see https://github.com/bolt/bolt/pull/6596
        'Twig_SimpleTest' => 'Twig_Test',
    ]]]);
};
