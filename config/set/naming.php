<?php

declare (strict_types=1);
namespace RectorPrefix202610;

use Rector\Config\RectorConfig;
use Rector\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector;
use Rector\Naming\Rector\ClassMethod\RenameVariableToMatchNewTypeRector;
use Rector\Naming\Rector\Foreach_\RenameForeachValueVariableToMatchExprVariableRector;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([RenameParamToMatchTypeRector::class, RenameVariableToMatchNewTypeRector::class, RenameForeachValueVariableToMatchExprVariableRector::class]);
};
