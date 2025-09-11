<?php

declare (strict_types=1);
namespace RectorPrefix202509;

use Rector\Config\RectorConfig;
use Rector\TypeDeclarationDocblocks\Rector\Class_\DocblockVarFromParamDocblockInConstructorRector;
use Rector\TypeDeclarationDocblocks\Rector\ClassMethod\DocblockGetterReturnArrayFromPropertyDocblockVarRector;
/**
 * @experimental * 2025-09, experimental hidden set for type declaration in docblocks
 */
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([DocblockVarFromParamDocblockInConstructorRector::class, DocblockGetterReturnArrayFromPropertyDocblockVarRector::class]);
};
