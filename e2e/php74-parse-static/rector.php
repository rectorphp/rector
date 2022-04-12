<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DowngradePhp72\Rector\FuncCall\DowngradeJsonDecodeNullAssociativeArgRector;
use Rector\Core\Configuration\Option;

return static function (RectorConfig $rectorConfig): void {
    $parameters = $rectorConfig->parameters();

    $parameters->set(Option::PARALLEL, true);
    $parameters->set(Option::PATHS, [
        __DIR__ . '/tests',
    ]);

    $services = $rectorConfig->services();
    $services->set(DowngradeJsonDecodeNullAssociativeArgRector::class);
};
