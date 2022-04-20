<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Removing\Rector\FuncCall\RemoveFuncCallRector;
use Rector\Removing\ValueObject\RemoveFuncCall;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig
        ->ruleWithConfiguration(RemoveFuncCallRector::class, [
            new RemoveFuncCall('ini_get', [
                0 => ['y2k_compliance', 'safe_mode', 'magic_quotes_runtime'],
            ]),
            new RemoveFuncCall('ini_set', [
                0 => ['y2k_compliance', 'safe_mode', 'magic_quotes_runtime'],
            ]),
        ]);
};
