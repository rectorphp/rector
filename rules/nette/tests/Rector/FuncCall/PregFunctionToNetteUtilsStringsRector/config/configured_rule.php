<?php

declare(strict_types=1);

use Rector\Nette\Rector\FuncCall\PregFunctionToNetteUtilsStringsRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(PregFunctionToNetteUtilsStringsRector::class);
};
