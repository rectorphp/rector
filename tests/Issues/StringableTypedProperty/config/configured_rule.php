<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php80\Rector\Class_\StringableForToStringRector;
use Rector\TypeDeclaration\Rector\Property\TypedPropertyFromStrictGetterMethodReturnTypeRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(StringableForToStringRector::class);
    $rectorConfig->rule(TypedPropertyFromStrictGetterMethodReturnTypeRector::class);
};
