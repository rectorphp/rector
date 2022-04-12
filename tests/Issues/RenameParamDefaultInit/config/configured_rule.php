<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector;
use Rector\Php56\Rector\FunctionLike\AddDefaultValueForUndefinedVariableRector;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(RenameParamToMatchTypeRector::class);
    $services->set(AddDefaultValueForUndefinedVariableRector::class);
};
