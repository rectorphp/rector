<?php

declare(strict_types=1);

use Rector\Core\Contract\Rector\RectorInterface;
use Rector\DowngradePhp72\Rector\ClassMethod\DowngradeParameterTypeWideningRector;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(DowngradeParameterTypeWideningRector::class)
        ->call('configure', [[
            DowngradeParameterTypeWideningRector::SAFE_TYPES => [RectorInterface::class],
            DowngradeParameterTypeWideningRector::SAFE_TYPES_TO_METHODS => [
                ContainerInterface::class => ['setParameter', 'getParameter', 'hasParameter'],
            ],
        ]]);
};
