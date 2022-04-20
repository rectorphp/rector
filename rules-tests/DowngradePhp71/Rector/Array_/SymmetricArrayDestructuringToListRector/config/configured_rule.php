<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DowngradePhp71\Rector\Array_\SymmetricArrayDestructuringToListRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(SymmetricArrayDestructuringToListRector::class);
};
