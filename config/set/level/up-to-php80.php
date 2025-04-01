<?php

declare (strict_types=1);
namespace RectorPrefix202504;

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->sets([SetList::PHP_80, LevelSetList::UP_TO_PHP_74]);
};
