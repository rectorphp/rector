<?php

declare (strict_types=1);
namespace RectorPrefix202510;

use Rector\Config\RectorConfig;
use Rector\Doctrine\TypedCollections\Rector\Class_\CompletePropertyDocblockFromToManyRector;
use Rector\Doctrine\TypedCollections\Rector\ClassMethod\CollectionDocblockGenericTypeRector;
use Rector\Doctrine\TypedCollections\Rector\ClassMethod\DefaultCollectionKeyRector;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([
        // safe rules that handle only docblocks
        CollectionDocblockGenericTypeRector::class,
        DefaultCollectionKeyRector::class,
        CompletePropertyDocblockFromToManyRector::class,
    ]);
};
