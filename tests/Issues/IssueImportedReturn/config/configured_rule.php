<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Privatization\Rector\Class_\RepeatedLiteralToClassConstantRector;
use Rector\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(RepeatedLiteralToClassConstantRector::class);
    $services->set(ReturnTypeDeclarationRector::class);

    $rectorConfig->importNames();
};
