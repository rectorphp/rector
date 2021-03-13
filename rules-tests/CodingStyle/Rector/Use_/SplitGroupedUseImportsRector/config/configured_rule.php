<?php

declare(strict_types=1);

use Rector\CodingStyle\Rector\Use_\SplitGroupedUseImportsRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(SplitGroupedUseImportsRector::class);
};
