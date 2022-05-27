<?php

declare (strict_types=1);
namespace RectorPrefix20220527;

use Rector\Config\RectorConfig;
use Ssch\TYPO3Rector\Rector\v12\v0\MigrateNullFlagRector;
use Ssch\TYPO3Rector\Rector\v12\v0\MigrateRequiredFlagRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/../config.php');
    $rectorConfig->rule(MigrateNullFlagRector::class);
    $rectorConfig->rule(MigrateRequiredFlagRector::class);
};
