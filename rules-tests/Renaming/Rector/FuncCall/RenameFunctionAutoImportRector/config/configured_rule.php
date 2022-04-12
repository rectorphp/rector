<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Core\Configuration\Option;
use Rector\Renaming\Rector\FuncCall\RenameFunctionRector;

return static function (RectorConfig $rectorConfig): void {
    $parameters = $rectorConfig->parameters();
    $parameters->set(Option::AUTO_IMPORT_NAMES, true);

    $services = $rectorConfig->services();
    $services->set(RenameFunctionRector::class)
        ->configure([
            'service' => 'Symfony\Component\DependencyInjection\Loader\Configurator\service',
        ]);
};
