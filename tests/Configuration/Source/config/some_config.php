<?php

declare(strict_types=1);

use Rector\Core\Tests\Configuration\Source\CustomLocalRector;
use Rector\Php72\Rector\Assign\ListEachRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ListEachRector::class);
    $services->set(CustomLocalRector::class);
};
