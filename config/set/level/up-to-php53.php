<?php

declare (strict_types=1);
namespace RectorPrefix202410;

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\SetList;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->sets([SetList::PHP_53, SetList::PHP_52]);
};
