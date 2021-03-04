<?php

declare(strict_types=1);

use Rector\PHPUnit\Rector\MethodCall\AssertPropertyExistsRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(AssertPropertyExistsRector::class);
};
