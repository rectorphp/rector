<?php

declare (strict_types=1);
namespace RectorPrefix202305;

use Rector\Config\RectorConfig;
use Rector\PHPUnit\Rector\Class_\StaticDataProviderClassMethodRector;
use Rector\PHPUnit\Rector\ClassLike\RemoveTestSuffixFromAbstractTestClassesRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/annotations-to-attributes.php');
    $rectorConfig->rules([StaticDataProviderClassMethodRector::class, RemoveTestSuffixFromAbstractTestClassesRector::class]);
};
