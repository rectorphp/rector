<?php

declare(strict_types=1);

use Rector\PHPUnit\Rector\MethodCall\AssertComparisonToSpecificMethodRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(AssertComparisonToSpecificMethodRector::class);
};
