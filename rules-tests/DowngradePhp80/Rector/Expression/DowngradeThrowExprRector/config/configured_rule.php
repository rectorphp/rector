<?php

declare(strict_types=1);

use Rector\DowngradePhp80\Rector\Expression\DowngradeThrowExprRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(DowngradeThrowExprRector::class);
};
