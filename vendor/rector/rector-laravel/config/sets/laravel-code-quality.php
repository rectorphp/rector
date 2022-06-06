<?php

declare (strict_types=1);
namespace RectorPrefix20220606;

use RectorPrefix20220606\Rector\Config\RectorConfig;
use RectorPrefix20220606\Rector\Laravel\Rector\Assign\CallOnAppArrayAccessToStandaloneAssignRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(CallOnAppArrayAccessToStandaloneAssignRector::class);
};
