<?php

declare (strict_types=1);
namespace RectorPrefix20220606;

use RectorPrefix20220606\Rector\Config\RectorConfig;
use RectorPrefix20220606\Ssch\TYPO3Rector\FileProcessor\Resources\Icons\IconsFileProcessor;
use RectorPrefix20220606\Ssch\TYPO3Rector\FileProcessor\Resources\Icons\Rector\IconsRector;
use RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v8\v3\RefactorMethodFileContentRector;
use RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v8\v3\RefactorQueryViewTableWrapRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/../config.php');
    $rectorConfig->rule(RefactorMethodFileContentRector::class);
    $rectorConfig->rule(RefactorQueryViewTableWrapRector::class);
    $rectorConfig->rule(IconsRector::class);
    $services = $rectorConfig->services();
    $services->set(IconsFileProcessor::class)->autowire();
};
