<?php

declare (strict_types=1);
namespace RectorPrefix20220501;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/../config.php');
    $rectorConfig->ruleWithConfiguration(\Rector\Renaming\Rector\Name\RenameClassRector::class, ['TYPO3\\CMS\\Core\\Tests\\UnitTestCase' => 'TYPO3\\TestingFramework\\Core\\Unit\\UnitTestCase', 'TYPO3\\CMS\\Core\\Tests\\FunctionalTestCase' => 'TYPO3\\TestingFramework\\Core\\Functional\\FunctionalTestCase']);
};
