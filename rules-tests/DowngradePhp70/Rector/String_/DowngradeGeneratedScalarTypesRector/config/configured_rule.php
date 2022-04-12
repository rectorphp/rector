<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DowngradePhp70\Rector\FunctionLike\DowngradeScalarTypeDeclarationRector;
use Rector\DowngradePhp70\Rector\String_\DowngradeGeneratedScalarTypesRector;
use Rector\DowngradePhp71\Rector\FunctionLike\DowngradeVoidTypeDeclarationRector;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(DowngradeGeneratedScalarTypesRector::class);

    // dependendent rules
    $services->set(DowngradeScalarTypeDeclarationRector::class);
    $services->set(DowngradeVoidTypeDeclarationRector::class);
};
