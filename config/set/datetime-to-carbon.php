<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Carbon\Rector\FuncCall\DateFuncCallToCarbonRector;
use Rector\Carbon\Rector\FuncCall\TimeFuncCallToCarbonRector;
use Rector\Carbon\Rector\MethodCall\DateTimeMethodCallToCarbonRector;
use Rector\Carbon\Rector\New_\DateTimeInstanceToCarbonRector;
use Rector\Config\RectorConfig;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([DateFuncCallToCarbonRector::class, DateTimeInstanceToCarbonRector::class, DateTimeMethodCallToCarbonRector::class, TimeFuncCallToCarbonRector::class]);
};
