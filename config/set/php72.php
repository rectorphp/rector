<?php

declare (strict_types=1);
namespace RectorPrefix20220606;

use RectorPrefix20220606\Rector\Config\RectorConfig;
use RectorPrefix20220606\Rector\Php72\Rector\Assign\ListEachRector;
use RectorPrefix20220606\Rector\Php72\Rector\Assign\ReplaceEachAssignmentWithKeyCurrentRector;
use RectorPrefix20220606\Rector\Php72\Rector\FuncCall\CreateFunctionToAnonymousFunctionRector;
use RectorPrefix20220606\Rector\Php72\Rector\FuncCall\GetClassOnNullRector;
use RectorPrefix20220606\Rector\Php72\Rector\FuncCall\IsObjectOnIncompleteClassRector;
use RectorPrefix20220606\Rector\Php72\Rector\FuncCall\ParseStrWithResultArgumentRector;
use RectorPrefix20220606\Rector\Php72\Rector\FuncCall\StringifyDefineRector;
use RectorPrefix20220606\Rector\Php72\Rector\FuncCall\StringsAssertNakedRector;
use RectorPrefix20220606\Rector\Php72\Rector\Unset_\UnsetCastRector;
use RectorPrefix20220606\Rector\Php72\Rector\While_\WhileEachToForeachRector;
use RectorPrefix20220606\Rector\Renaming\Rector\FuncCall\RenameFunctionRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(WhileEachToForeachRector::class);
    $rectorConfig->rule(ListEachRector::class);
    $rectorConfig->rule(ReplaceEachAssignmentWithKeyCurrentRector::class);
    $rectorConfig->rule(UnsetCastRector::class);
    $rectorConfig->ruleWithConfiguration(RenameFunctionRector::class, [
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
    $rectorConfig->rule(GetClassOnNullRector::class);
    $rectorConfig->rule(IsObjectOnIncompleteClassRector::class);
    $rectorConfig->rule(ParseStrWithResultArgumentRector::class);
    $rectorConfig->rule(StringsAssertNakedRector::class);
    $rectorConfig->rule(CreateFunctionToAnonymousFunctionRector::class);
    $rectorConfig->rule(StringifyDefineRector::class);
};
