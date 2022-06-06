<?php

declare (strict_types=1);
namespace RectorPrefix20220606;

use RectorPrefix20220606\Rector\Config\RectorConfig;
use RectorPrefix20220606\Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use RectorPrefix20220606\Rector\Renaming\Rector\Name\RenameClassRector;
use RectorPrefix20220606\Rector\Renaming\ValueObject\MethodCallRename;
use RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v10\v3\SubstituteResourceFactoryRector;
use RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v10\v3\UseClassTypo3VersionRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/../config.php');
    $rectorConfig->rule(UseClassTypo3VersionRector::class);
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [new MethodCallRename('TYPO3\\CMS\\Linkvalidator\\Repository\\BrokenLinkRepository', 'getNumberOfBrokenLinks', 'isLinkTargetBrokenLink')]);
    $rectorConfig->rule(SubstituteResourceFactoryRector::class);
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, ['TYPO3\\CMS\\Extbase\\Mvc\\Web\\Request' => 'TYPO3\\CMS\\Extbase\\Mvc\\Request', 'TYPO3\\CMS\\Extbase\\Mvc\\Web\\Response' => 'TYPO3\\CMS\\Extbase\\Mvc\\Response']);
};
