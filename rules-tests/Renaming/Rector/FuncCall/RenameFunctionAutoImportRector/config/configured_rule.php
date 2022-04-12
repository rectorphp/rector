<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\FuncCall\RenameFunctionRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->importNames();

    $services = $rectorConfig->services();
    $services->set(RenameFunctionRector::class)
        ->configure([
            'service' => 'Symfony\Component\DependencyInjection\Loader\Configurator\service',
        ]);
};
