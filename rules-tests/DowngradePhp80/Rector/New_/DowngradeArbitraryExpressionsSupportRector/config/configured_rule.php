<?php

declare(strict_types=1);

use Rector\DowngradePhp80\Rector\New_\DowngradeArbitraryExpressionsSupportRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(DowngradeArbitraryExpressionsSupportRector::class);
};
