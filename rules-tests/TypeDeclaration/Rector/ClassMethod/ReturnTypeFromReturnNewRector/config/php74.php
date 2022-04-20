<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromReturnNewRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(ReturnTypeFromReturnNewRector::class);

    $rectorConfig->phpVersion(PhpVersionFeature::STATIC_RETURN_TYPE - 1);
};
