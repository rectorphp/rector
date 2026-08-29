<?php

declare (strict_types=1);
namespace RectorPrefix202608;

use Rector\CodeQuality\Rector\If_\CombineIfRector;
use Rector\CodeQuality\Rector\If_\ExplicitBoolCompareRector;
use Rector\Config\RectorConfig;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([ExplicitBoolCompareRector::class, CombineIfRector::class]);
};
