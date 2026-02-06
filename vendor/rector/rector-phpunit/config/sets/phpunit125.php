<?php

declare (strict_types=1);
namespace RectorPrefix202602;

use Rector\Config\RectorConfig;
use Rector\PHPUnit\PHPUnit120\Rector\Class_\AllowMockObjectsForDataProviderRector;
use Rector\PHPUnit\PHPUnit120\Rector\Class_\AllowMockObjectsWhereParentClassRector;
use Rector\PHPUnit\PHPUnit120\Rector\Class_\AllowMockObjectsWithoutExpectationsAttributeRector;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([
        AllowMockObjectsWhereParentClassRector::class,
        AllowMockObjectsForDataProviderRector::class,
        // experimental, from PHPUnit 12.5.2
        // @see https://github.com/sebastianbergmann/phpunit/commit/24c208d6a340c3071f28a9b5cce02b9377adfd43
        AllowMockObjectsWithoutExpectationsAttributeRector::class,
    ]);
};
