<?php

declare (strict_types=1);
namespace RectorPrefix20220418;

use Rector\Config\RectorConfig;
use Rector\Php54\Rector\Break_\RemoveZeroBreakContinueRector;
use Rector\Php54\Rector\FuncCall\RemoveReferenceFromCallRector;
use Rector\Renaming\Rector\FuncCall\RenameFunctionRector;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $services = $rectorConfig->services();
    $services->set(\Rector\Renaming\Rector\FuncCall\RenameFunctionRector::class)->configure(['mysqli_param_count' => 'mysqli_stmt_param_count']);
    $services->set(\Rector\Php54\Rector\FuncCall\RemoveReferenceFromCallRector::class);
    $services->set(\Rector\Php54\Rector\Break_\RemoveZeroBreakContinueRector::class);
};
