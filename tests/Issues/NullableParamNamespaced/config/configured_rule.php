<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Core\Configuration\Option;
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodParameterRector;
use Rector\DeadCode\Rector\Stmt\RemoveUnreachableStatementRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddArrayParamDocTypeRector;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(AddArrayParamDocTypeRector::class);
    $services->set(RemoveUnusedPrivateMethodParameterRector::class);
    $services->set(RemoveUnreachableStatementRector::class);

    $parameters = $rectorConfig->parameters();
    $parameters->set(Option::AUTO_IMPORT_NAMES, true);
};
