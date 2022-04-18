<?php

declare (strict_types=1);
namespace RectorPrefix20220418;

use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersion;
use Rector\Set\ValueObject\SetList;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->import(\Rector\Set\ValueObject\SetList::PHP_54);
    $rectorConfig->import(\Rector\Set\ValueObject\SetList::PHP_53);
    // parameter must be defined after import, to override impored param version
    $rectorConfig->phpVersion(\Rector\Core\ValueObject\PhpVersion::PHP_54);
};
