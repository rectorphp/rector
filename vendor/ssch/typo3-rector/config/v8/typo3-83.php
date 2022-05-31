<?php

declare (strict_types=1);
namespace RectorPrefix20220531;

use Rector\Config\RectorConfig;
use Ssch\TYPO3Rector\FileProcessor\Resources\Icons\IconsFileProcessor;
use Ssch\TYPO3Rector\FileProcessor\Resources\Icons\Rector\IconsRector;
use Ssch\TYPO3Rector\Rector\v8\v3\RefactorMethodFileContentRector;
use Ssch\TYPO3Rector\Rector\v8\v3\RefactorQueryViewTableWrapRector;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/../config.php');
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v8\v3\RefactorMethodFileContentRector::class);
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v8\v3\RefactorQueryViewTableWrapRector::class);
    $rectorConfig->rule(\Ssch\TYPO3Rector\FileProcessor\Resources\Icons\Rector\IconsRector::class);
    $services = $rectorConfig->services();
    $services->set(\Ssch\TYPO3Rector\FileProcessor\Resources\Icons\IconsFileProcessor::class)->autowire();
};
