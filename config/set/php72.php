<?php

declare (strict_types=1);
namespace RectorPrefix20220501;

use Rector\Config\RectorConfig;
use Rector\Php72\Rector\Assign\ListEachRector;
use Rector\Php72\Rector\Assign\ReplaceEachAssignmentWithKeyCurrentRector;
use Rector\Php72\Rector\FuncCall\CreateFunctionToAnonymousFunctionRector;
use Rector\Php72\Rector\FuncCall\GetClassOnNullRector;
use Rector\Php72\Rector\FuncCall\IsObjectOnIncompleteClassRector;
use Rector\Php72\Rector\FuncCall\ParseStrWithResultArgumentRector;
use Rector\Php72\Rector\FuncCall\StringifyDefineRector;
use Rector\Php72\Rector\FuncCall\StringsAssertNakedRector;
use Rector\Php72\Rector\Unset_\UnsetCastRector;
use Rector\Php72\Rector\While_\WhileEachToForeachRector;
use Rector\Renaming\Rector\FuncCall\RenameFunctionRector;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(\Rector\Php72\Rector\While_\WhileEachToForeachRector::class);
    $rectorConfig->rule(\Rector\Php72\Rector\Assign\ListEachRector::class);
    $rectorConfig->rule(\Rector\Php72\Rector\Assign\ReplaceEachAssignmentWithKeyCurrentRector::class);
    $rectorConfig->rule(\Rector\Php72\Rector\Unset_\UnsetCastRector::class);
    $rectorConfig->ruleWithConfiguration(\Rector\Renaming\Rector\FuncCall\RenameFunctionRector::class, [
        # and imagewbmp
        'jpeg2wbmp' => 'imagecreatefromjpeg',
        # or imagewbmp
        'png2wbmp' => 'imagecreatefrompng',
        #migration72.deprecated.gmp_random-function
        # http://php.net/manual/en/migration72.deprecated.php
        # or gmp_random_range
        'gmp_random' => 'gmp_random_bits',
        'read_exif_data' => 'exif_read_data',
    ]);
    $rectorConfig->rule(\Rector\Php72\Rector\FuncCall\GetClassOnNullRector::class);
    $rectorConfig->rule(\Rector\Php72\Rector\FuncCall\IsObjectOnIncompleteClassRector::class);
    $rectorConfig->rule(\Rector\Php72\Rector\FuncCall\ParseStrWithResultArgumentRector::class);
    $rectorConfig->rule(\Rector\Php72\Rector\FuncCall\StringsAssertNakedRector::class);
    $rectorConfig->rule(\Rector\Php72\Rector\FuncCall\CreateFunctionToAnonymousFunctionRector::class);
    $rectorConfig->rule(\Rector\Php72\Rector\FuncCall\StringifyDefineRector::class);
};
