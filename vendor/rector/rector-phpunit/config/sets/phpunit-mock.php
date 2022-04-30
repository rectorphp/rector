<?php

declare (strict_types=1);
namespace RectorPrefix20220430;

use Rector\Config\RectorConfig;
use Rector\PHPUnit\Rector\MethodCall\UseSpecificWillMethodRector;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(\Rector\PHPUnit\Rector\MethodCall\UseSpecificWillMethodRector::class);
};
