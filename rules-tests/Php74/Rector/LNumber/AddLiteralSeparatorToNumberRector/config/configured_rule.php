<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php74\Rector\LNumber\AddLiteralSeparatorToNumberRector;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(AddLiteralSeparatorToNumberRector::class)
        ->configure([
            AddLiteralSeparatorToNumberRector::LIMIT_VALUE => 1_000_000,
        ]);
};
