<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Equal\UseIdenticalOverEqualWithSameTypeRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(UseIdenticalOverEqualWithSameTypeRector::class);
};
