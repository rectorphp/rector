<?php

declare (strict_types=1);
namespace RectorPrefix202208;

use Rector\Config\RectorConfig;
use Rector\Symfony\Rector\MethodCall\ChangeCollectionTypeOptionNameFromTypeToEntryTypeRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(ChangeCollectionTypeOptionNameFromTypeToEntryTypeRector::class);
};
