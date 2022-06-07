<?php

declare (strict_types=1);
namespace RectorPrefix20220607;

use Rector\CakePHP\Rector\MethodCall\ModalToGetSetRector;
use Rector\CakePHP\Rector\Property\ChangeSnakedFixtureNameToPascalRector;
use Rector\CakePHP\ValueObject\ModalToGetSet;
use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Transform\Rector\Assign\PropertyFetchToMethodCallRector;
use Rector\Transform\Rector\MethodCall\MethodCallToAnotherMethodCallWithArgumentsRector;
use Rector\Transform\ValueObject\MethodCallToAnotherMethodCallWithArguments;
use Rector\Transform\ValueObject\PropertyFetchToMethodCall;
# source: https://book.cakephp.org/3.0/en/appendices/3-7-migration-guide.html
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [new MethodCallRename('RectorPrefix20220607\\Cake\\Form\\Form', 'errors', 'getErrors'), new MethodCallRename('RectorPrefix20220607\\Cake\\Validation\\Validation', 'cc', 'creditCard'), new MethodCallRename('RectorPrefix20220607\\Cake\\Filesystem\\Folder', 'normalizePath', 'correctSlashFor'), new MethodCallRename('RectorPrefix20220607\\Cake\\Http\\Client\\Response', 'body', 'getStringBody'), new MethodCallRename('RectorPrefix20220607\\Cake\\Core\\Plugin', 'unload', 'clear')]);
    $rectorConfig->ruleWithConfiguration(PropertyFetchToMethodCallRector::class, [new PropertyFetchToMethodCall('RectorPrefix20220607\\Cake\\Http\\Client\\Response', 'body', 'getStringBody'), new PropertyFetchToMethodCall('RectorPrefix20220607\\Cake\\Http\\Client\\Response', 'json', 'getJson'), new PropertyFetchToMethodCall('RectorPrefix20220607\\Cake\\Http\\Client\\Response', 'xml', 'getXml'), new PropertyFetchToMethodCall('RectorPrefix20220607\\Cake\\Http\\Client\\Response', 'cookies', 'getCookies'), new PropertyFetchToMethodCall('RectorPrefix20220607\\Cake\\Http\\Client\\Response', 'code', 'getStatusCode'), new PropertyFetchToMethodCall('RectorPrefix20220607\\Cake\\View\\View', 'request', 'getRequest', 'setRequest'), new PropertyFetchToMethodCall('RectorPrefix20220607\\Cake\\View\\View', 'response', 'getResponse', 'setResponse'), new PropertyFetchToMethodCall('RectorPrefix20220607\\Cake\\View\\View', 'templatePath', 'getTemplatePath', 'setTemplatePath'), new PropertyFetchToMethodCall('RectorPrefix20220607\\Cake\\View\\View', 'template', 'getTemplate', 'setTemplate'), new PropertyFetchToMethodCall('RectorPrefix20220607\\Cake\\View\\View', 'layout', 'getLayout', 'setLayout'), new PropertyFetchToMethodCall('RectorPrefix20220607\\Cake\\View\\View', 'layoutPath', 'getLayoutPath', 'setLayoutPath'), new PropertyFetchToMethodCall('RectorPrefix20220607\\Cake\\View\\View', 'autoLayout', 'isAutoLayoutEnabled', 'enableAutoLayout'), new PropertyFetchToMethodCall('RectorPrefix20220607\\Cake\\View\\View', 'theme', 'getTheme', 'setTheme'), new PropertyFetchToMethodCall('RectorPrefix20220607\\Cake\\View\\View', 'subDir', 'getSubDir', 'setSubDir'), new PropertyFetchToMethodCall('RectorPrefix20220607\\Cake\\View\\View', 'plugin', 'getPlugin', 'setPlugin'), new PropertyFetchToMethodCall('RectorPrefix20220607\\Cake\\View\\View', 'name', 'getName', 'setName'), new PropertyFetchToMethodCall('RectorPrefix20220607\\Cake\\View\\View', 'elementCache', 'getElementCache', 'setElementCache'), new PropertyFetchToMethodCall('RectorPrefix20220607\\Cake\\View\\View', 'helpers', 'helpers')]);
    $rectorConfig->ruleWithConfiguration(MethodCallToAnotherMethodCallWithArgumentsRector::class, [new MethodCallToAnotherMethodCallWithArguments('RectorPrefix20220607\\Cake\\Database\\Query', 'join', 'clause', ['join']), new MethodCallToAnotherMethodCallWithArguments('RectorPrefix20220607\\Cake\\Database\\Query', 'from', 'clause', ['from'])]);
    $rectorConfig->ruleWithConfiguration(ModalToGetSetRector::class, [new ModalToGetSet('RectorPrefix20220607\\Cake\\Database\\Connection', 'logQueries', 'isQueryLoggingEnabled', 'enableQueryLogging'), new ModalToGetSet('RectorPrefix20220607\\Cake\\ORM\\Association', 'className', 'getClassName', 'setClassName')]);
    $rectorConfig->rule(ChangeSnakedFixtureNameToPascalRector::class);
};
