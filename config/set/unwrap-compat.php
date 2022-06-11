<?php

declare (strict_types=1);
namespace RectorPrefix202206;

use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\If_\UnwrapFutureCompatibleIfFunctionExistsRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(UnwrapFutureCompatibleIfFunctionExistsRector::class);
};
