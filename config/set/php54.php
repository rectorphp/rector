<?php

declare (strict_types=1);
namespace RectorPrefix202308;

use Rector\Config\RectorConfig;
use Rector\Php54\Rector\Array_\LongArrayToShortArrayRector;
use Rector\Php54\Rector\Break_\RemoveZeroBreakContinueRector;
use Rector\Php54\Rector\FuncCall\RemoveReferenceFromCallRector;
use Rector\Renaming\Rector\FuncCall\RenameFunctionRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(LongArrayToShortArrayRector::class);
    $rectorConfig->ruleWithConfiguration(RenameFunctionRector::class, ['mysqli_param_count' => 'mysqli_stmt_param_count']);
    $rectorConfig->rule(RemoveReferenceFromCallRector::class);
    $rectorConfig->rule(RemoveZeroBreakContinueRector::class);
};
