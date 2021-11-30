<?php

declare(strict_types=1);

use Rector\Renaming\Rector\FuncCall\RenameFunctionRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(RenameFunctionRector::class)
        ->configure([
            'view' => 'Laravel\Templating\render',
            'sprintf' => 'Safe\sprintf',
        ]);
};
