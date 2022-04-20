<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\FunctionLike\RemoveAlwaysTrueConditionSetInConstructorRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(RemoveAlwaysTrueConditionSetInConstructorRector::class);
};
