<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php74\Rector\LNumber\AddLiteralSeparatorToNumberRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig
        ->ruleWithConfiguration(AddLiteralSeparatorToNumberRector::class, [
            AddLiteralSeparatorToNumberRector::LIMIT_VALUE => 1_000_000,
        ]);
};
