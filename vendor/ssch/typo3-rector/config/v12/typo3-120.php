<?php

declare (strict_types=1);
namespace RectorPrefix20220510;

use Rector\Config\RectorConfig;
use Ssch\TYPO3Rector\Rector\v12\v0\MigrateInternalTypeRector;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/../config.php');
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v12\v0\MigrateInternalTypeRector::class);
};
