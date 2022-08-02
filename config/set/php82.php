<?php

declare (strict_types=1);
namespace RectorPrefix202208;

use Rector\Config\RectorConfig;
use Rector\Php82\Rector\Class_\ReadOnlyClassRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(ReadOnlyClassRector::class);
};
