<?php

declare (strict_types=1);
namespace RectorPrefix20220501;

use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersion;
use Rector\DowngradePhp53\Rector\Dir\DirConstToFileConstRector;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->phpVersion(\Rector\Core\ValueObject\PhpVersion::PHP_52);
    $rectorConfig->rule(\Rector\DowngradePhp53\Rector\Dir\DirConstToFileConstRector::class);
};
