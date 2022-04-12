<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodParameterRector;
use Rector\DeadCode\Rector\Stmt\RemoveUnreachableStatementRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddArrayParamDocTypeRector;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(AddArrayParamDocTypeRector::class);
    $services->set(RemoveUnusedPrivateMethodParameterRector::class);
    $services->set(RemoveUnreachableStatementRector::class);

    $rectorConfig->importNames();
};
