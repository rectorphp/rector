<?php

declare (strict_types=1);
namespace RectorPrefix202304;

use Rector\Config\RectorConfig;
use Rector\Naming\Rector\Assign\RenameVariableToMatchMethodCallReturnTypeRector;
use Rector\Naming\Rector\Class_\RenamePropertyToMatchTypeRector;
use Rector\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector;
use Rector\Naming\Rector\ClassMethod\RenameVariableToMatchNewTypeRector;
use Rector\Naming\Rector\Foreach_\RenameForeachValueVariableToMatchExprVariableRector;
use Rector\Naming\Rector\Foreach_\RenameForeachValueVariableToMatchMethodCallReturnTypeRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(RenameParamToMatchTypeRector::class);
    $rectorConfig->rule(RenamePropertyToMatchTypeRector::class);
    $rectorConfig->rule(RenameVariableToMatchNewTypeRector::class);
    $rectorConfig->rule(RenameVariableToMatchMethodCallReturnTypeRector::class);
    $rectorConfig->rule(RenameForeachValueVariableToMatchMethodCallReturnTypeRector::class);
    $rectorConfig->rule(RenameForeachValueVariableToMatchExprVariableRector::class);
};
