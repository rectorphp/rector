<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DowngradePhp70\Rector\FunctionLike\DowngradeScalarTypeDeclarationRector;
use Rector\DowngradePhp70\Rector\String_\DowngradeGeneratedScalarTypesRector;
use Rector\DowngradePhp71\Rector\FunctionLike\DowngradeVoidTypeDeclarationRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(DowngradeGeneratedScalarTypesRector::class);

    // dependendent rules
    $rectorConfig->rule(DowngradeScalarTypeDeclarationRector::class);
    $rectorConfig->rule(DowngradeVoidTypeDeclarationRector::class);
};
