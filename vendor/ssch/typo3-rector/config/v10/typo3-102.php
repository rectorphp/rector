<?php

declare (strict_types=1);
namespace RectorPrefix20220501;

use Rector\Config\RectorConfig;
use Ssch\TYPO3Rector\Rector\v10\v2\ExcludeServiceKeysToArrayRector;
use Ssch\TYPO3Rector\Rector\v10\v2\InjectEnvironmentServiceIfNeededInResponseRector;
use Ssch\TYPO3Rector\Rector\v10\v2\MoveApplicationContextToEnvironmentApiRector;
use Ssch\TYPO3Rector\Rector\v10\v2\UseActionControllerRector;
use Ssch\TYPO3Rector\Rector\v10\v2\UseTypo3InformationForCopyRightNoticeRector;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/../config.php');
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v10\v2\MoveApplicationContextToEnvironmentApiRector::class);
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v10\v2\ExcludeServiceKeysToArrayRector::class);
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v10\v2\UseActionControllerRector::class);
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v10\v2\UseTypo3InformationForCopyRightNoticeRector::class);
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v10\v2\InjectEnvironmentServiceIfNeededInResponseRector::class);
};
