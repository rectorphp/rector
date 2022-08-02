<?php

declare (strict_types=1);
namespace RectorPrefix202208;

use Rector\Config\RectorConfig;
use Rector\Laravel\Rector\Assign\CallOnAppArrayAccessToStandaloneAssignRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(CallOnAppArrayAccessToStandaloneAssignRector::class);
};
