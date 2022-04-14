<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\If_\SimplifyIfElseToTernaryRector;
use Rector\CodingStyle\Rector\Property\InlineSimplePropertyAnnotationRector;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\Assign\RemoveUnusedVariableAssignRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->importNames();
    $services = $rectorConfig->services();
    $services->set(SimplifyIfElseToTernaryRector::class);
    $services->set(InlineSimplePropertyAnnotationRector::class);
    $services->set(RemoveUnusedVariableAssignRector::class);
};
