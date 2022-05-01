<?php

declare (strict_types=1);
namespace RectorPrefix20220501;

use Rector\Config\RectorConfig;
use Rector\Php71\Rector\Assign\AssignArrayToStringRector;
use Rector\Php71\Rector\BinaryOp\BinaryOpBetweenNumberAndStringRector;
use Rector\Php71\Rector\BooleanOr\IsIterableRector;
use Rector\Php71\Rector\ClassConst\PublicConstantVisibilityRector;
use Rector\Php71\Rector\FuncCall\CountOnNullRector;
use Rector\Php71\Rector\FuncCall\RemoveExtraParametersRector;
use Rector\Php71\Rector\List_\ListToArrayDestructRector;
use Rector\Php71\Rector\TryCatch\MultiExceptionCatchRector;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(\Rector\Php71\Rector\BooleanOr\IsIterableRector::class);
    $rectorConfig->rule(\Rector\Php71\Rector\TryCatch\MultiExceptionCatchRector::class);
    $rectorConfig->rule(\Rector\Php71\Rector\Assign\AssignArrayToStringRector::class);
    $rectorConfig->rule(\Rector\Php71\Rector\FuncCall\CountOnNullRector::class);
    $rectorConfig->rule(\Rector\Php71\Rector\FuncCall\RemoveExtraParametersRector::class);
    $rectorConfig->rule(\Rector\Php71\Rector\BinaryOp\BinaryOpBetweenNumberAndStringRector::class);
    $rectorConfig->rule(\Rector\Php71\Rector\List_\ListToArrayDestructRector::class);
    $rectorConfig->rule(\Rector\Php71\Rector\ClassConst\PublicConstantVisibilityRector::class);
};
