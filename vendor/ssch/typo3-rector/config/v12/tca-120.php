<?php

declare (strict_types=1);
namespace RectorPrefix20220606;

use RectorPrefix20220606\Rector\Config\RectorConfig;
use RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v12\v0\MigrateNullFlagRector;
use RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v12\v0\MigrateRequiredFlagRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/../config.php');
    $rectorConfig->rule(MigrateNullFlagRector::class);
    $rectorConfig->rule(MigrateRequiredFlagRector::class);
};
