<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector;
use Rector\Php56\Rector\FunctionLike\AddDefaultValueForUndefinedVariableRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(RenameParamToMatchTypeRector::class);
    $rectorConfig->rule(AddDefaultValueForUndefinedVariableRector::class);
};
