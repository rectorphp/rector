<?php

declare (strict_types=1);
namespace RectorPrefix202304;

use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersion;
use Rector\DowngradePhp82\Rector\Class_\DowngradeReadonlyClassRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->phpVersion(PhpVersion::PHP_81);
    $rectorConfig->rule(DowngradeReadonlyClassRector::class);
};
