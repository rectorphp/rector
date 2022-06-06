<?php

declare (strict_types=1);
namespace RectorPrefix20220606;

use RectorPrefix20220606\Rector\Config\RectorConfig;
use RectorPrefix20220606\Rector\Symfony\Set\SymfonyLevelSetList;
use RectorPrefix20220606\Rector\Symfony\Set\SymfonySetList;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->sets([SymfonySetList::SYMFONY_50, SymfonySetList::SYMFONY_50_TYPES, SymfonyLevelSetList::UP_TO_SYMFONY_44]);
};
