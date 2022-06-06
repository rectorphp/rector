<?php

declare (strict_types=1);
namespace RectorPrefix20220606;

use RectorPrefix20220606\Rector\Config\RectorConfig;
use RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v10\v2\ExcludeServiceKeysToArrayRector;
use RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v10\v2\InjectEnvironmentServiceIfNeededInResponseRector;
use RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v10\v2\MoveApplicationContextToEnvironmentApiRector;
use RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v10\v2\UseActionControllerRector;
use RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v10\v2\UseTypo3InformationForCopyRightNoticeRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/../config.php');
    $rectorConfig->rule(MoveApplicationContextToEnvironmentApiRector::class);
    $rectorConfig->rule(ExcludeServiceKeysToArrayRector::class);
    $rectorConfig->rule(UseActionControllerRector::class);
    $rectorConfig->rule(UseTypo3InformationForCopyRightNoticeRector::class);
    $rectorConfig->rule(InjectEnvironmentServiceIfNeededInResponseRector::class);
};
