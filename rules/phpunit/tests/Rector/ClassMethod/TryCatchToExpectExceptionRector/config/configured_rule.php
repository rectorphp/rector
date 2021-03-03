<?php

declare(strict_types=1);

use Rector\PHPUnit\Rector\ClassMethod\TryCatchToExpectExceptionRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(TryCatchToExpectExceptionRector::class);
};
