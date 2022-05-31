<?php

declare (strict_types=1);
namespace RectorPrefix20220531;

use Rector\Config\RectorConfig;
use Rector\Php54\Rector\Break_\RemoveZeroBreakContinueRector;
use Rector\Php54\Rector\FuncCall\RemoveReferenceFromCallRector;
use Rector\Renaming\Rector\FuncCall\RenameFunctionRector;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(\Rector\Renaming\Rector\FuncCall\RenameFunctionRector::class, ['mysqli_param_count' => 'mysqli_stmt_param_count']);
    $rectorConfig->rule(\Rector\Php54\Rector\FuncCall\RemoveReferenceFromCallRector::class);
    $rectorConfig->rule(\Rector\Php54\Rector\Break_\RemoveZeroBreakContinueRector::class);
};
