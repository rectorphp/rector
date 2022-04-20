<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\FuncCall\SimplifyStrposLowerRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(SimplifyStrposLowerRector::class);
};
