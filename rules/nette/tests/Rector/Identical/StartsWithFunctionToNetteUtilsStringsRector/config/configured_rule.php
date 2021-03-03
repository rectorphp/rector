<?php

declare(strict_types=1);

use Rector\Nette\Rector\Identical\StartsWithFunctionToNetteUtilsStringsRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(StartsWithFunctionToNetteUtilsStringsRector::class);
};
