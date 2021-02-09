<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
<<<<<<< HEAD
use Rector\Php72\Rector\FuncCall\GetClassOnNullRector;
=======
>>>>>>> 495b7788a... use more configs
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::AUTO_IMPORT_NAMES, true);

    $services = $containerConfigurator->services();
<<<<<<< HEAD
    $services->set(GetClassOnNullRector::class);
=======
    $services->set(\Rector\Php72\Rector\FuncCall\GetClassOnNullRector::class);
>>>>>>> 495b7788a... use more configs
};
