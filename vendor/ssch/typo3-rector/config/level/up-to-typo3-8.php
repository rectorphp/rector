<?php

declare (strict_types=1);
namespace RectorPrefix20220501;

use Rector\Config\RectorConfig;
use Ssch\TYPO3Rector\Set\Typo3LevelSetList;
use Ssch\TYPO3Rector\Set\Typo3SetList;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->sets([\Ssch\TYPO3Rector\Set\Typo3LevelSetList::UP_TO_TYPO3_7, \Ssch\TYPO3Rector\Set\Typo3SetList::TYPO3_87]);
};
