<?php

declare (strict_types=1);
namespace RectorPrefix202305;

use Rector\Config\RectorConfig;
use Rector\Symfony\Set\SymfonyLevelSetList;
use Rector\Symfony\Set\SymfonySetList;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->sets([SymfonySetList::SYMFONY_50, SymfonySetList::SYMFONY_50_TYPES, SymfonyLevelSetList::UP_TO_SYMFONY_44]);
};
