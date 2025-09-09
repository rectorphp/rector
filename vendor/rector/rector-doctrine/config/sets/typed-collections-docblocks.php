<?php

declare (strict_types=1);
namespace RectorPrefix202509;

use Rector\Doctrine\TypedCollections\Rector\ClassMethod\CollectionDocblockGenericTypeRector;
use Rector\Config\RectorConfig;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([
        // safe rules that handle only docblocks
        CollectionDocblockGenericTypeRector::class,
    ]);
};
