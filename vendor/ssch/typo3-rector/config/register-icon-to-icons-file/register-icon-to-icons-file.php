<?php

declare (strict_types=1);
namespace RectorPrefix20220501;

use Rector\Config\RectorConfig;
use Ssch\TYPO3Rector\Rector\v11\v5\RegisterIconToIconFileRector;
use Ssch\TYPO3Rector\Rector\v11\v5\RegisterIconToIconFileRector\AddIconsToReturnRector;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/../config.php');
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v11\v5\RegisterIconToIconFileRector\AddIconsToReturnRector::class);
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v11\v5\RegisterIconToIconFileRector::class);
};
