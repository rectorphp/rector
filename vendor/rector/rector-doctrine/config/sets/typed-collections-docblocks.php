<?php

declare (strict_types=1);
namespace RectorPrefix202509;

use Rector\Config\RectorConfig;
use Rector\Doctrine\TypedCollections\Rector\ClassMethod\CollectionDocblockGenericTypeRector;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([
        // safe rules that handle only docblocks
        CollectionDocblockGenericTypeRector::class,
    ]);
};
