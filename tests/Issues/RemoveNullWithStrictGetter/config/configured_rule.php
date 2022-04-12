<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersion;
use Rector\DeadCode\Rector\PropertyProperty\RemoveNullPropertyInitializationRector;
use Rector\Php74\Rector\Property\TypedPropertyRector;
use Rector\TypeDeclaration\Rector\Property\TypedPropertyFromStrictGetterMethodReturnTypeRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(TypedPropertyRector::class);

    $rectorConfig->rule(TypedPropertyFromStrictGetterMethodReturnTypeRector::class);

    // should be ignored if typed property is used
    $rectorConfig->rule(RemoveNullPropertyInitializationRector::class);

    $rectorConfig->phpVersion(PhpVersion::PHP_74);
};
