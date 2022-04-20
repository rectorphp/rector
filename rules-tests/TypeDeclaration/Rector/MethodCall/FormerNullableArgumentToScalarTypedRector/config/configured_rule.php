<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\TypeDeclaration\Rector\MethodCall\FormerNullableArgumentToScalarTypedRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(FormerNullableArgumentToScalarTypedRector::class);
};
