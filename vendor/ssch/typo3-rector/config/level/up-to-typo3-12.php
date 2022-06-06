<?php

declare (strict_types=1);
namespace RectorPrefix20220606;

use RectorPrefix20220606\Rector\Config\RectorConfig;
use RectorPrefix20220606\Ssch\TYPO3Rector\Set\Typo3LevelSetList;
use RectorPrefix20220606\Ssch\TYPO3Rector\Set\Typo3SetList;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->sets([Typo3LevelSetList::UP_TO_TYPO3_11, Typo3SetList::TYPO3_12]);
};
