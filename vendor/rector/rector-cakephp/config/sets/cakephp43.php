<?php

declare (strict_types=1);
namespace RectorPrefix202208;

use Rector\CakePHP\Rector\MethodCall\RemoveIntermediaryMethodRector;
use Rector\CakePHP\ValueObject\RemoveIntermediaryMethod;
use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Transform\Rector\Assign\PropertyFetchToMethodCallRector;
use Rector\Transform\Rector\MethodCall\MethodCallToAnotherMethodCallWithArgumentsRector;
use Rector\Transform\ValueObject\MethodCallToAnotherMethodCallWithArguments;
use Rector\Transform\ValueObject\PropertyFetchToMethodCall;
# source: https://book.cakephp.org/4.next/en/appendices/4-3-migration-guide.html
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [new MethodCallRename('Cake\\Controller\\Component', 'shutdown', 'afterFilter')]);
    $rectorConfig->ruleWithConfiguration(PropertyFetchToMethodCallRector::class, [new PropertyFetchToMethodCall('Cake\\Network\\Socket', 'connected', 'isConnected'), new PropertyFetchToMethodCall('Cake\\Network\\Socket', 'encrypted', 'isEncrypted'), new PropertyFetchToMethodCall('Cake\\Network\\Socket', 'lastError', 'lastError')]);
    $rectorConfig->ruleWithConfiguration(RemoveIntermediaryMethodRector::class, [new RemoveIntermediaryMethod('getTableLocator', 'get', 'fetchTable')]);
    $rectorConfig->ruleWithConfiguration(MethodCallToAnotherMethodCallWithArgumentsRector::class, [new MethodCallToAnotherMethodCallWithArguments('Cake\\Database\\DriverInterface', 'supportsQuoting', 'supports', ['quote']), new MethodCallToAnotherMethodCallWithArguments('Cake\\Database\\DriverInterface', 'supportsSavepoints', 'supports', ['savepoint'])]);
};
