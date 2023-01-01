<?php

declare (strict_types=1);
namespace RectorPrefix202301;

use Rector\Config\RectorConfig;
use Rector\PHPUnit\Rector\Class_\StaticDataProviderClassMethodRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/annotations-to-attributes.php');
    $rectorConfig->rules([StaticDataProviderClassMethodRector::class]);
};
