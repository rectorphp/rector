<?php

declare (strict_types=1);
namespace RectorPrefix20220606;

use RectorPrefix20220606\Rector\CakePHP\Rector\MethodCall\RemoveIntermediaryMethodRector;
use RectorPrefix20220606\Rector\CakePHP\ValueObject\RemoveIntermediaryMethod;
use RectorPrefix20220606\Rector\Config\RectorConfig;
use RectorPrefix20220606\Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use RectorPrefix20220606\Rector\Renaming\ValueObject\MethodCallRename;
use RectorPrefix20220606\Rector\Transform\Rector\Assign\PropertyFetchToMethodCallRector;
use RectorPrefix20220606\Rector\Transform\Rector\MethodCall\MethodCallToAnotherMethodCallWithArgumentsRector;
use RectorPrefix20220606\Rector\Transform\ValueObject\MethodCallToAnotherMethodCallWithArguments;
use RectorPrefix20220606\Rector\Transform\ValueObject\PropertyFetchToMethodCall;
# source: https://book.cakephp.org/4.next/en/appendices/4-3-migration-guide.html
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [new MethodCallRename('Cake\\Controller\\Component', 'shutdown', 'afterFilter')]);
    $rectorConfig->ruleWithConfiguration(PropertyFetchToMethodCallRector::class, [new PropertyFetchToMethodCall('Cake\\Network\\Socket', 'connected', 'isConnected'), new PropertyFetchToMethodCall('Cake\\Network\\Socket', 'encrypted', 'isEncrypted'), new PropertyFetchToMethodCall('Cake\\Network\\Socket', 'lastError', 'lastError')]);
    $rectorConfig->ruleWithConfiguration(RemoveIntermediaryMethodRector::class, [new RemoveIntermediaryMethod('getTableLocator', 'get', 'fetchTable')]);
    $rectorConfig->ruleWithConfiguration(MethodCallToAnotherMethodCallWithArgumentsRector::class, [new MethodCallToAnotherMethodCallWithArguments('Cake\\Database\\DriverInterface', 'supportsQuoting', 'supports', ['quote']), new MethodCallToAnotherMethodCallWithArguments('Cake\\Database\\DriverInterface', 'supportsSavepoints', 'supports', ['savepoint'])]);
};
