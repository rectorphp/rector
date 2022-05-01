<?php

declare (strict_types=1);
namespace RectorPrefix20220501;

use Rector\Config\RectorConfig;
use Rector\Symfony\Set\SymfonyLevelSetList;
use Rector\Symfony\Set\SymfonySetList;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->sets([\Rector\Symfony\Set\SymfonySetList::SYMFONY_52, \Rector\Symfony\Set\SymfonySetList::SYMFONY_52_VALIDATOR_ATTRIBUTES, \Rector\Symfony\Set\SymfonySetList::ANNOTATIONS_TO_ATTRIBUTES, \Rector\Symfony\Set\SymfonyLevelSetList::UP_TO_SYMFONY_51]);
};
