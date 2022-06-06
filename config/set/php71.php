<?php

declare (strict_types=1);
namespace RectorPrefix20220606;

use RectorPrefix20220606\Rector\Config\RectorConfig;
use RectorPrefix20220606\Rector\Php71\Rector\Assign\AssignArrayToStringRector;
use RectorPrefix20220606\Rector\Php71\Rector\BinaryOp\BinaryOpBetweenNumberAndStringRector;
use RectorPrefix20220606\Rector\Php71\Rector\BooleanOr\IsIterableRector;
use RectorPrefix20220606\Rector\Php71\Rector\ClassConst\PublicConstantVisibilityRector;
use RectorPrefix20220606\Rector\Php71\Rector\FuncCall\CountOnNullRector;
use RectorPrefix20220606\Rector\Php71\Rector\FuncCall\RemoveExtraParametersRector;
use RectorPrefix20220606\Rector\Php71\Rector\List_\ListToArrayDestructRector;
use RectorPrefix20220606\Rector\Php71\Rector\TryCatch\MultiExceptionCatchRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(IsIterableRector::class);
    $rectorConfig->rule(MultiExceptionCatchRector::class);
    $rectorConfig->rule(AssignArrayToStringRector::class);
    $rectorConfig->rule(CountOnNullRector::class);
    $rectorConfig->rule(RemoveExtraParametersRector::class);
    $rectorConfig->rule(BinaryOpBetweenNumberAndStringRector::class);
    $rectorConfig->rule(ListToArrayDestructRector::class);
    $rectorConfig->rule(PublicConstantVisibilityRector::class);
};
