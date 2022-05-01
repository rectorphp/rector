<?php

declare (strict_types=1);
namespace RectorPrefix20220501;

use Rector\Config\RectorConfig;
use Ssch\TYPO3Rector\Rector\v9\v5\ExtbaseCommandControllerToSymfonyCommand\AddArgumentToSymfonyCommandRector;
use Ssch\TYPO3Rector\Rector\v9\v5\ExtbaseCommandControllerToSymfonyCommand\AddCommandsToReturnRector;
use Ssch\TYPO3Rector\Rector\v9\v5\ExtbaseCommandControllerToSymfonyCommandRector;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/../config.php');
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v9\v5\ExtbaseCommandControllerToSymfonyCommand\AddArgumentToSymfonyCommandRector::class);
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v9\v5\ExtbaseCommandControllerToSymfonyCommand\AddCommandsToReturnRector::class);
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v9\v5\ExtbaseCommandControllerToSymfonyCommandRector::class);
};
