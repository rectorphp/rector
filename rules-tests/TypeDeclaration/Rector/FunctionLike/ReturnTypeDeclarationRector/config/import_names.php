<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->phpVersion(PhpVersionFeature::STATIC_RETURN_TYPE - 1);

    $rectorConfig->phpstanConfig(__DIR__ . '/../../../../../../phpstan-for-rector.neon');
    $rectorConfig->importNames();
    $rectorConfig->rule(ReturnTypeDeclarationRector::class);
};
