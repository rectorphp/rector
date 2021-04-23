<?php

declare(strict_types=1);

use Rector\CodingStyle\Rector\FuncCall\PreslashSimpleFunctionRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(PreslashSimpleFunctionRector::class);
};
