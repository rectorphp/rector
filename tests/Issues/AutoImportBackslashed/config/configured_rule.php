<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\If_\ExplicitBoolCompareRector;
use Rector\Config\RectorConfig;
use Rector\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(ExplicitBoolCompareRector::class);
    $rectorConfig->rule(ReturnTypeDeclarationRector::class);

    $rectorConfig->importNames();
};
