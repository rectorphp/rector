<?php

declare (strict_types=1);
namespace RectorPrefix20220606;

use RectorPrefix20220606\Rector\Config\RectorConfig;
use RectorPrefix20220606\Rector\PHPUnit\Rector\MethodCall\ReplaceAssertArraySubsetWithDmsPolyfillRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(ReplaceAssertArraySubsetWithDmsPolyfillRector::class);
};
