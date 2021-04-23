<?php

declare(strict_types=1);

use Rector\EarlyReturn\Rector\If_\ChangeOrIfContinueToMultiContinueRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(ChangeOrIfContinueToMultiContinueRector::class);
};
