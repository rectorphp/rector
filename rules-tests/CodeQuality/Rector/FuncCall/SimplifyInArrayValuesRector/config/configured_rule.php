<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\FuncCall\SimplifyInArrayValuesRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(SimplifyInArrayValuesRector::class);
};
