<?php

declare(strict_types=1);

use Rector\DowngradePhp70\Rector\GroupUse\SplitGroupedUseImportsRector;

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(SplitGroupedUseImportsRector::class);
};
