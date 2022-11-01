<?php

declare (strict_types=1);
namespace RectorPrefix202211;

use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersion;
use Rector\Set\ValueObject\SetList;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->sets([SetList::PHP_54, SetList::PHP_53]);
    // parameter must be defined after import, to override imported param version
    $rectorConfig->phpVersion(PhpVersion::PHP_54);
};
