<?php

declare (strict_types=1);
namespace RectorPrefix20220607;

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
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [new MethodCallRename('RectorPrefix20220607\\Cake\\Controller\\Component', 'shutdown', 'afterFilter')]);
    $rectorConfig->ruleWithConfiguration(PropertyFetchToMethodCallRector::class, [new PropertyFetchToMethodCall('RectorPrefix20220607\\Cake\\Network\\Socket', 'connected', 'isConnected'), new PropertyFetchToMethodCall('RectorPrefix20220607\\Cake\\Network\\Socket', 'encrypted', 'isEncrypted'), new PropertyFetchToMethodCall('RectorPrefix20220607\\Cake\\Network\\Socket', 'lastError', 'lastError')]);
    $rectorConfig->ruleWithConfiguration(RemoveIntermediaryMethodRector::class, [new RemoveIntermediaryMethod('getTableLocator', 'get', 'fetchTable')]);
    $rectorConfig->ruleWithConfiguration(MethodCallToAnotherMethodCallWithArgumentsRector::class, [new MethodCallToAnotherMethodCallWithArguments('RectorPrefix20220607\\Cake\\Database\\DriverInterface', 'supportsQuoting', 'supports', ['quote']), new MethodCallToAnotherMethodCallWithArguments('RectorPrefix20220607\\Cake\\Database\\DriverInterface', 'supportsSavepoints', 'supports', ['savepoint'])]);
};
