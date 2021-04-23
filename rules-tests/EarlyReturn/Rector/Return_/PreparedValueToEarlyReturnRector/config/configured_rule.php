<?php

declare(strict_types=1);

use Rector\EarlyReturn\Rector\Return_\PreparedValueToEarlyReturnRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(PreparedValueToEarlyReturnRector::class);
};
