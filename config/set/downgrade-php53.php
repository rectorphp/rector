<?php

declare (strict_types=1);
namespace RectorPrefix20220606;

use RectorPrefix20220606\Rector\Config\RectorConfig;
use RectorPrefix20220606\Rector\Core\ValueObject\PhpVersion;
use RectorPrefix20220606\Rector\DowngradePhp53\Rector\Dir\DirConstToFileConstRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->phpVersion(PhpVersion::PHP_52);
    $rectorConfig->rule(DirConstToFileConstRector::class);
};
