<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DowngradePhp72\Rector\FuncCall\DowngradePregUnmatchedAsNullConstantRector;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(DowngradePregUnmatchedAsNullConstantRector::class);
};
