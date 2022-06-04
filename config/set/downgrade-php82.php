<?php

declare (strict_types=1);
namespace RectorPrefix20220604;

use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersion;
use Rector\DowngradePhp82\Rector\Class_\DowngradeReadonlyClassRector;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->phpVersion(\Rector\Core\ValueObject\PhpVersion::PHP_81);
    $rectorConfig->rule(\Rector\DowngradePhp82\Rector\Class_\DowngradeReadonlyClassRector::class);
};
