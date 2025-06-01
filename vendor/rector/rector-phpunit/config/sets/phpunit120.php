<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Config\RectorConfig;
use Rector\PHPUnit\PHPUnit120\Rector\Class_\RemoveOverrideFinalConstructTestCaseRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(RemoveOverrideFinalConstructTestCaseRector::class);
};
