<?php

declare (strict_types=1);
namespace RectorPrefix202312;

use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersion;
use Rector\DowngradePhp82\Rector\Class_\DowngradeReadonlyClassRector;
use Rector\DowngradePhp82\Rector\FunctionLike\DowngradeStandaloneNullTrueFalseReturnTypeRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->phpVersion(PhpVersion::PHP_81);
    $rectorConfig->rules([DowngradeReadonlyClassRector::class, DowngradeStandaloneNullTrueFalseReturnTypeRector::class]);
};
