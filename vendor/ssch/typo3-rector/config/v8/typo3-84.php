<?php

declare (strict_types=1);
namespace RectorPrefix20220501;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Ssch\TYPO3Rector\Rector\v8\v4\ExtensionManagementUtilityExtRelPathRector;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/../config.php');
    // @see https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/8.4/Deprecation-75363-DeprecateFormResultCompilerJStop.html
    $rectorConfig->ruleWithConfiguration(\Rector\Renaming\Rector\MethodCall\RenameMethodRector::class, [new \Rector\Renaming\ValueObject\MethodCallRename('TYPO3\\CMS\\Backend\\Routing\\FormResultCompiler', 'JStop', 'addCssFiles')]);
    // @see https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/8.4/Deprecation-77826-RTEHtmlAreaSpellcheckerEntrypoint.html
    $rectorConfig->ruleWithConfiguration(\Rector\Renaming\Rector\MethodCall\RenameMethodRector::class, [new \Rector\Renaming\ValueObject\MethodCallRename('TYPO3\\CMS\\Saltedpasswords\\Salt\\SpellCheckingController', 'main', 'processRequest')]);
    $rectorConfig->rule(\Ssch\TYPO3Rector\Rector\v8\v4\ExtensionManagementUtilityExtRelPathRector::class);
};
