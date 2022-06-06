<?php

declare (strict_types=1);
namespace RectorPrefix20220606;

use RectorPrefix20220606\Rector\Config\RectorConfig;
use RectorPrefix20220606\Rector\Php54\Rector\Break_\RemoveZeroBreakContinueRector;
use RectorPrefix20220606\Rector\Php54\Rector\FuncCall\RemoveReferenceFromCallRector;
use RectorPrefix20220606\Rector\Renaming\Rector\FuncCall\RenameFunctionRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameFunctionRector::class, ['mysqli_param_count' => 'mysqli_stmt_param_count']);
    $rectorConfig->rule(RemoveReferenceFromCallRector::class);
    $rectorConfig->rule(RemoveZeroBreakContinueRector::class);
};
