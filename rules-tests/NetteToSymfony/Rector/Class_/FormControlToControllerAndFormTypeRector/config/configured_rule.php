<?php

declare(strict_types=1);

use Rector\NetteToSymfony\Rector\Class_\FormControlToControllerAndFormTypeRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(FormControlToControllerAndFormTypeRector::class);
};
