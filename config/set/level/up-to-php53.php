<?php

declare (strict_types=1);
namespace RectorPrefix202401;

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\SetList;
use Rector\ValueObject\PhpVersion;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->sets([SetList::PHP_53, SetList::PHP_52]);
    // parameter must be defined after import, to override imported param version
    $rectorConfig->phpVersion(PhpVersion::PHP_53);
};
