<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php71\Rector\Assign\AssignArrayToStringRector;
use Rector\Php71\Rector\BinaryOp\BinaryOpBetweenNumberAndStringRector;
use Rector\Php71\Rector\BooleanOr\IsIterableRector;
use Rector\Php71\Rector\ClassConst\PublicConstantVisibilityRector;
use Rector\Php71\Rector\FuncCall\CountOnNullRector;
use Rector\Php71\Rector\FuncCall\RemoveExtraParametersRector;
use Rector\Php71\Rector\List_\ListToArrayDestructRector;
use Rector\Php71\Rector\TryCatch\MultiExceptionCatchRector;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(IsIterableRector::class);
    $services->set(MultiExceptionCatchRector::class);
    $services->set(AssignArrayToStringRector::class);
    $services->set(CountOnNullRector::class);
    $services->set(RemoveExtraParametersRector::class);
    $services->set(BinaryOpBetweenNumberAndStringRector::class);
    $services->set(ListToArrayDestructRector::class);
    $services->set(PublicConstantVisibilityRector::class);
};
