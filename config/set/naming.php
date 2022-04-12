<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Naming\Rector\Assign\RenameVariableToMatchMethodCallReturnTypeRector;
use Rector\Naming\Rector\Class_\RenamePropertyToMatchTypeRector;
use Rector\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector;
use Rector\Naming\Rector\ClassMethod\RenameVariableToMatchNewTypeRector;
use Rector\Naming\Rector\Foreach_\RenameForeachValueVariableToMatchExprVariableRector;
use Rector\Naming\Rector\Foreach_\RenameForeachValueVariableToMatchMethodCallReturnTypeRector;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(RenameParamToMatchTypeRector::class);
    $services->set(RenamePropertyToMatchTypeRector::class);
    $services->set(RenameVariableToMatchNewTypeRector::class);
    $services->set(RenameVariableToMatchMethodCallReturnTypeRector::class);
    $services->set(RenameForeachValueVariableToMatchMethodCallReturnTypeRector::class);
    $services->set(RenameForeachValueVariableToMatchExprVariableRector::class);
};
