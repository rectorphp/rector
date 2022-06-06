<?php

declare (strict_types=1);
namespace RectorPrefix20220606;

use RectorPrefix20220606\Rector\Config\RectorConfig;
use RectorPrefix20220606\Rector\Naming\Rector\Assign\RenameVariableToMatchMethodCallReturnTypeRector;
use RectorPrefix20220606\Rector\Naming\Rector\Class_\RenamePropertyToMatchTypeRector;
use RectorPrefix20220606\Rector\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector;
use RectorPrefix20220606\Rector\Naming\Rector\ClassMethod\RenameVariableToMatchNewTypeRector;
use RectorPrefix20220606\Rector\Naming\Rector\Foreach_\RenameForeachValueVariableToMatchExprVariableRector;
use RectorPrefix20220606\Rector\Naming\Rector\Foreach_\RenameForeachValueVariableToMatchMethodCallReturnTypeRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(RenameParamToMatchTypeRector::class);
    $rectorConfig->rule(RenamePropertyToMatchTypeRector::class);
    $rectorConfig->rule(RenameVariableToMatchNewTypeRector::class);
    $rectorConfig->rule(RenameVariableToMatchMethodCallReturnTypeRector::class);
    $rectorConfig->rule(RenameForeachValueVariableToMatchMethodCallReturnTypeRector::class);
    $rectorConfig->rule(RenameForeachValueVariableToMatchExprVariableRector::class);
};
