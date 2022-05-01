<?php

declare (strict_types=1);
namespace RectorPrefix20220501;

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
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(\Rector\Renaming\Rector\MethodCall\RenameMethodRector::class, [new \Rector\Renaming\ValueObject\MethodCallRename('Cake\\Controller\\Component', 'shutdown', 'afterFilter')]);
    $rectorConfig->ruleWithConfiguration(\Rector\Transform\Rector\Assign\PropertyFetchToMethodCallRector::class, [new \Rector\Transform\ValueObject\PropertyFetchToMethodCall('Cake\\Network\\Socket', 'connected', 'isConnected'), new \Rector\Transform\ValueObject\PropertyFetchToMethodCall('Cake\\Network\\Socket', 'encrypted', 'isEncrypted'), new \Rector\Transform\ValueObject\PropertyFetchToMethodCall('Cake\\Network\\Socket', 'lastError', 'lastError')]);
    $rectorConfig->ruleWithConfiguration(\Rector\CakePHP\Rector\MethodCall\RemoveIntermediaryMethodRector::class, [new \Rector\CakePHP\ValueObject\RemoveIntermediaryMethod('getTableLocator', 'get', 'fetchTable')]);
    $rectorConfig->ruleWithConfiguration(\Rector\Transform\Rector\MethodCall\MethodCallToAnotherMethodCallWithArgumentsRector::class, [new \Rector\Transform\ValueObject\MethodCallToAnotherMethodCallWithArguments('Cake\\Database\\DriverInterface', 'supportsQuoting', 'supports', ['quote']), new \Rector\Transform\ValueObject\MethodCallToAnotherMethodCallWithArguments('Cake\\Database\\DriverInterface', 'supportsSavepoints', 'supports', ['savepoint'])]);
};
