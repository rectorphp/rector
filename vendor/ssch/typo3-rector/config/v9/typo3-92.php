<?php

declare (strict_types=1);
namespace RectorPrefix20220527;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Ssch\TYPO3Rector\Rector\v9\v2\GeneralUtilityGetUrlRequestHeadersRector;
use Ssch\TYPO3Rector\Rector\v9\v2\PageNotFoundAndErrorHandlingRector;
use Ssch\TYPO3Rector\Rector\v9\v2\RenameMethodCallToEnvironmentMethodCallRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/../config.php');
    $rectorConfig->rule(RenameMethodCallToEnvironmentMethodCallRector::class);
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, ['TYPO3\\CMS\\Core\\Cache\\Frontend\\StringFrontend' => 'TYPO3\\CMS\\Core\\Cache\\Frontend\\VariableFrontend']);
    $rectorConfig->rule(GeneralUtilityGetUrlRequestHeadersRector::class);
    $rectorConfig->rule(PageNotFoundAndErrorHandlingRector::class);
};
