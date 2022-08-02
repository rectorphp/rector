<?php

declare (strict_types=1);
namespace RectorPrefix202208;

use Rector\Config\RectorConfig;
use Rector\Php71\Rector\Assign\AssignArrayToStringRector;
use Rector\Php71\Rector\BinaryOp\BinaryOpBetweenNumberAndStringRector;
use Rector\Php71\Rector\BooleanOr\IsIterableRector;
use Rector\Php71\Rector\ClassConst\PublicConstantVisibilityRector;
use Rector\Php71\Rector\FuncCall\CountOnNullRector;
use Rector\Php71\Rector\FuncCall\RemoveExtraParametersRector;
use Rector\Php71\Rector\List_\ListToArrayDestructRector;
use Rector\Php71\Rector\TryCatch\MultiExceptionCatchRector;
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
