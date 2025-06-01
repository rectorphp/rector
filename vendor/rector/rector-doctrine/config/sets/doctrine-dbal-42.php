<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Config\RectorConfig;
use Rector\Doctrine\Dbal42\Rector\New_\AddArrayResultColumnNamesRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([
        // @see https://github.com/doctrine/dbal/pull/6504/files
        AddArrayResultColumnNamesRector::class,
    ]);
};
