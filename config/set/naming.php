<?php

declare (strict_types=1);
namespace RectorPrefix202410;

use Rector\Config\RectorConfig;
use Rector\Naming\Rector\Assign\RenameVariableToMatchMethodCallReturnTypeRector;
use Rector\Naming\Rector\Class_\RenamePropertyToMatchTypeRector;
use Rector\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector;
use Rector\Naming\Rector\ClassMethod\RenameVariableToMatchNewTypeRector;
use Rector\Naming\Rector\Foreach_\RenameForeachValueVariableToMatchExprVariableRector;
use Rector\Naming\Rector\Foreach_\RenameForeachValueVariableToMatchMethodCallReturnTypeRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([RenameParamToMatchTypeRector::class, RenamePropertyToMatchTypeRector::class, RenameVariableToMatchNewTypeRector::class, RenameVariableToMatchMethodCallReturnTypeRector::class, RenameForeachValueVariableToMatchMethodCallReturnTypeRector::class, RenameForeachValueVariableToMatchExprVariableRector::class]);
};
