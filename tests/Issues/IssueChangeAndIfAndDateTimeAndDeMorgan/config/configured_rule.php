<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\BooleanNot\SimplifyDeMorganBinaryRector;
use Rector\CodeQuality\Rector\ClassMethod\DateTimeToDateTimeInterfaceRector;
use Rector\EarlyReturn\Rector\If_\ChangeAndIfToEarlyReturnRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(SimplifyDeMorganBinaryRector::class);
    $services->set(ChangeAndIfToEarlyReturnRector::class);
    $services->set(DateTimeToDateTimeInterfaceRector::class);
};
