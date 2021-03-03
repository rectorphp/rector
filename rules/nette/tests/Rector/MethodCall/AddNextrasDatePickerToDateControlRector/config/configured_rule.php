<?php

declare(strict_types=1);

use Rector\Nette\Rector\MethodCall\AddNextrasDatePickerToDateControlRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(AddNextrasDatePickerToDateControlRector::class);
};
