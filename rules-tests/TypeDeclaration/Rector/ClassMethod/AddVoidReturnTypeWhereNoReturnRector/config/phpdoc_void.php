<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\TypeDeclaration\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();

    $services->set(AddVoidReturnTypeWhereNoReturnRector::class)
        ->configure([
            AddVoidReturnTypeWhereNoReturnRector::USE_PHPDOC => true,
        ]);
};
