<?php

declare (strict_types=1);
namespace RectorPrefix202312;

use Rector\Config\RectorConfig;
use Rector\Php71\Rector\Assign\AssignArrayToStringRector;
use Rector\Php71\Rector\BinaryOp\BinaryOpBetweenNumberAndStringRector;
use Rector\Php71\Rector\BooleanOr\IsIterableRector;
use Rector\Php71\Rector\ClassConst\PublicConstantVisibilityRector;
use Rector\Php71\Rector\FuncCall\RemoveExtraParametersRector;
use Rector\Php71\Rector\List_\ListToArrayDestructRector;
use Rector\Php71\Rector\TryCatch\MultiExceptionCatchRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([IsIterableRector::class, MultiExceptionCatchRector::class, AssignArrayToStringRector::class, RemoveExtraParametersRector::class, BinaryOpBetweenNumberAndStringRector::class, ListToArrayDestructRector::class, PublicConstantVisibilityRector::class]);
};
