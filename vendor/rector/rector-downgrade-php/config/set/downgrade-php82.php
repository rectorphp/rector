<?php

declare (strict_types=1);
namespace RectorPrefix202609;

use Rector\Config\RectorConfig;
use Rector\DowngradePhp82\Rector\Class_\DowngradeReadonlyClassRector;
use Rector\DowngradePhp82\Rector\Class_\DowngradeUnionIntersectionRector;
use Rector\DowngradePhp82\Rector\FuncCall\DowngradeIteratorCountToArrayRector;
use Rector\DowngradePhp82\Rector\FunctionLike\DowngradeStandaloneNullTrueFalseReturnTypeRector;
use Rector\DowngradePhp82\Rector\MethodCall\DowngradeReflectionMethodHasPrototypeRector;
use Rector\ValueObject\PhpVersion;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->phpVersion(PhpVersion::PHP_81);
    $rectorConfig->rules([DowngradeReadonlyClassRector::class, DowngradeStandaloneNullTrueFalseReturnTypeRector::class, DowngradeIteratorCountToArrayRector::class, DowngradeUnionIntersectionRector::class, DowngradeReflectionMethodHasPrototypeRector::class]);
};
