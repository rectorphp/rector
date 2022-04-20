<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Privatization\Rector\Class_\RepeatedLiteralToClassConstantRector;
use Rector\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(RepeatedLiteralToClassConstantRector::class);
    $rectorConfig->rule(ReturnTypeDeclarationRector::class);

    $rectorConfig->importNames();
};
