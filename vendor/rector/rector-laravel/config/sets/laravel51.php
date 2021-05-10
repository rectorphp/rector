<?php

declare (strict_types=1);
namespace RectorPrefix20210510;

use Rector\Renaming\Rector\Name\RenameClassRector;
use RectorPrefix20210510\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
# see: https://laravel.com/docs/5.1/upgrade
return static function (ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    $services->set(RenameClassRector::class)->call('configure', [[RenameClassRector::OLD_TO_NEW_CLASSES => ['Illuminate\\Validation\\Validator' => 'Illuminate\\Contracts\\Validation\\Validator']]]);
};
