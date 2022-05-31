<?php

declare (strict_types=1);
namespace RectorPrefix20220531;

use Rector\Config\RectorConfig;
use Ssch\TYPO3Rector\Rector\v12\v0\MigrateNullFlagRector;
use Ssch\TYPO3Rector\Rector\v12\v0\MigrateRequiredFlagRector;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/../config.php');
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v12\v0\MigrateNullFlagRector::class);
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v12\v0\MigrateRequiredFlagRector::class);
};
