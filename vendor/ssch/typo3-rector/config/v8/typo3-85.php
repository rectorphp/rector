<?php

declare (strict_types=1);
namespace RectorPrefix20211020;

use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\StaticCall\RenameStaticMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Renaming\ValueObject\RenameStaticMethod;
use Ssch\TYPO3Rector\Rector\v8\v5\CharsetConverterToMultiByteFunctionsRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $containerConfigurator->import(__DIR__ . '/../config.php');
    $services = $containerConfigurator->services();
    $services->set('clip_board_print_content_from_tab_to_get_content_from_tab')->class(\Rector\Renaming\Rector\MethodCall\RenameMethodRector::class)->call('configure', [[\Rector\Renaming\Rector\MethodCall\RenameMethodRector::METHOD_CALL_RENAMES => \Symplify\SymfonyPhpConfig\ValueObjectInliner::inline([new \Rector\Renaming\ValueObject\MethodCallRename('TYPO3\\CMS\\Backend\\Clipboard\\ClipBoard', 'printContentFromTab', 'getContentFromTab')])]]);
    $services->set(\Ssch\TYPO3Rector\Rector\v8\v5\CharsetConverterToMultiByteFunctionsRector::class);
    $services->set('extbase_array_utility_methods_to_core_array_utility_methods')->class(\Rector\Renaming\Rector\StaticCall\RenameStaticMethodRector::class)->call('configure', [[\Rector\Renaming\Rector\StaticCall\RenameStaticMethodRector::OLD_TO_NEW_METHODS_BY_CLASSES => \Symplify\SymfonyPhpConfig\ValueObjectInliner::inline([new \Rector\Renaming\ValueObject\RenameStaticMethod('TYPO3\\CMS\\Extbase\\Utility\\ArrayUtility', 'integerExplode', 'TYPO3\\CMS\\Core\\Utility\\GeneralUtility', 'intExplode'), new \Rector\Renaming\ValueObject\RenameStaticMethod('TYPO3\\CMS\\Extbase\\Utility\\ArrayUtility', 'trimExplode', 'TYPO3\\CMS\\Core\\Utility\\GeneralUtility', 'trimExplode'), new \Rector\Renaming\ValueObject\RenameStaticMethod('TYPO3\\CMS\\Extbase\\Utility\\ArrayUtility', 'getValueByPath', 'TYPO3\\CMS\\Core\\Utility\\ArrayUtility', 'getValueByPath'), new \Rector\Renaming\ValueObject\RenameStaticMethod('TYPO3\\CMS\\Extbase\\Utility\\ArrayUtility', 'setValueByPath', 'TYPO3\\CMS\\Core\\Utility\\ArrayUtility', 'setValueByPath'), new \Rector\Renaming\ValueObject\RenameStaticMethod('TYPO3\\CMS\\Extbase\\Utility\\ArrayUtility', 'unsetValueByPath', 'TYPO3\\CMS\\Core\\Utility\\ArrayUtility', 'removeByPath'), new \Rector\Renaming\ValueObject\RenameStaticMethod('TYPO3\\CMS\\Extbase\\Utility\\ArrayUtility', 'sortArrayWithIntegerKeys', 'TYPO3\\CMS\\Core\\Utility\\ArrayUtility', 'sortArrayWithIntegerKeys')])]]);
};
