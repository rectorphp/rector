<?php

declare (strict_types=1);
namespace RectorPrefix202208;

use Rector\Config\RectorConfig;
use Rector\PHPUnit\Rector\MethodCall\ReplaceAssertArraySubsetWithDmsPolyfillRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(ReplaceAssertArraySubsetWithDmsPolyfillRector::class);
};
