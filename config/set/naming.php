<?php

declare (strict_types=1);
namespace RectorPrefix202610;

use Rector\Config\RectorConfig;
use Rector\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector;
use Rector\Naming\Rector\ClassMethod\RenameVariableToMatchNewTypeRector;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([RenameParamToMatchTypeRector::class, RenameVariableToMatchNewTypeRector::class]);
};
