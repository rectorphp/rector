<?php

declare (strict_types=1);
namespace RectorPrefix20220501;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Ssch\TYPO3Rector\Rector\v10\v3\SubstituteResourceFactoryRector;
use Ssch\TYPO3Rector\Rector\v10\v3\UseClassTypo3VersionRector;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/../config.php');
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v10\v3\UseClassTypo3VersionRector::class);
    $rectorConfig->ruleWithConfiguration(\Rector\Renaming\Rector\MethodCall\RenameMethodRector::class, [new \Rector\Renaming\ValueObject\MethodCallRename('TYPO3\\CMS\\Linkvalidator\\Repository\\BrokenLinkRepository', 'getNumberOfBrokenLinks', 'isLinkTargetBrokenLink')]);
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v10\v3\SubstituteResourceFactoryRector::class);
    $rectorConfig->ruleWithConfiguration(\Rector\Renaming\Rector\Name\RenameClassRector::class, ['TYPO3\\CMS\\Extbase\\Mvc\\Web\\Request' => 'TYPO3\\CMS\\Extbase\\Mvc\\Request', 'TYPO3\\CMS\\Extbase\\Mvc\\Web\\Response' => 'TYPO3\\CMS\\Extbase\\Mvc\\Response']);
};
