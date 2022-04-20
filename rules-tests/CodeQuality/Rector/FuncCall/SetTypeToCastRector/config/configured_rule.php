<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\FuncCall\SetTypeToCastRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(SetTypeToCastRector::class);
};
