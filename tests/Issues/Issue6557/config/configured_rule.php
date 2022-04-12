<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\FunctionLike\RemoveOverriddenValuesRector;
use Rector\Naming\Rector\ClassMethod\RenameVariableToMatchNewTypeRector;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(RemoveOverriddenValuesRector::class);
    $services->set(RenameVariableToMatchNewTypeRector::class);
};
