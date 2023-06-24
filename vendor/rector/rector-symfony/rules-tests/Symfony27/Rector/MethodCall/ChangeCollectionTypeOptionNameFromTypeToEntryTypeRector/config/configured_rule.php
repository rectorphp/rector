<?php

declare (strict_types=1);
namespace RectorPrefix202306;

use Rector\Config\RectorConfig;
use Rector\Symfony\Symfony27\Rector\MethodCall\ChangeCollectionTypeOptionNameFromTypeToEntryTypeRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/../../../../../../config/config.php');
    $rectorConfig->rule(ChangeCollectionTypeOptionNameFromTypeToEntryTypeRector::class);
};
