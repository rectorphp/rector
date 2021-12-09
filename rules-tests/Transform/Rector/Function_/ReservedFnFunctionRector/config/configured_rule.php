<?php

declare(strict_types=1);

use Rector\Transform\Rector\Function_\ReservedFnFunctionRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(ReservedFnFunctionRector::class)
        ->configure([
            // for testing purposes of "fn" even on PHP 7.3-
            'reservedFn' => 'f',
        ]);
};
