<?php

declare(strict_types=1);

use Rector\Php56\Rector\FuncCall\PowToExpRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(PowToExpRector::class);
};
