<?php

declare (strict_types=1);
namespace RectorPrefix202411;

use Rector\Config\RectorConfig;
use Rector\PHPUnit\PHPUnit50\Rector\StaticCall\GetMockRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(GetMockRector::class);
};
