<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Core\Configuration\Option;
use Rector\Php72\Rector\FuncCall\GetClassOnNullRector;

return static function (RectorConfig $rectorConfig): void {
    $parameters = $rectorConfig->parameters();
    $parameters->set(Option::AUTO_IMPORT_NAMES, true);

    $services = $rectorConfig->services();
    $services->set(GetClassOnNullRector::class);
};
