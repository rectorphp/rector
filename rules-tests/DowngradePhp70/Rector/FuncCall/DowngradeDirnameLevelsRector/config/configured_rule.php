<?php

declare(strict_types=1);

use Rector\DowngradePhp70\Rector\FuncCall\DowngradeDirnameLevelsRector;

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(DowngradeDirnameLevelsRector::class);
};
