<?php

declare (strict_types=1);
namespace RectorPrefix202512;

use Rector\Config\RectorConfig;
use Rector\PHPUnit\PHPUnit120\Rector\Class_\AssertIsTypeMethodCallRector;
use Rector\PHPUnit\PHPUnit120\Rector\Class_\RemoveOverrideFinalConstructTestCaseRector;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([RemoveOverrideFinalConstructTestCaseRector::class, AssertIsTypeMethodCallRector::class]);
};
