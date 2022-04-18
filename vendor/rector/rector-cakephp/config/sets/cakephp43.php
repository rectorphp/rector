<?php

declare (strict_types=1);
namespace RectorPrefix20220418;

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
    $services = $rectorConfig->services();
    $services->set(\Rector\Renaming\Rector\MethodCall\RenameMethodRector::class)->configure([new \Rector\Renaming\ValueObject\MethodCallRename('Cake\\Controller\\Component', 'shutdown', 'afterFilter')]);
    $services->set(\Rector\Transform\Rector\Assign\PropertyFetchToMethodCallRector::class)->configure([new \Rector\Transform\ValueObject\PropertyFetchToMethodCall('Cake\\Network\\Socket', 'connected', 'isConnected'), new \Rector\Transform\ValueObject\PropertyFetchToMethodCall('Cake\\Network\\Socket', 'encrypted', 'isEncrypted'), new \Rector\Transform\ValueObject\PropertyFetchToMethodCall('Cake\\Network\\Socket', 'lastError', 'lastError')]);
    $services->set(\Rector\CakePHP\Rector\MethodCall\RemoveIntermediaryMethodRector::class)->configure([new \Rector\CakePHP\ValueObject\RemoveIntermediaryMethod('getTableLocator', 'get', 'fetchTable')]);
    $services->set(\Rector\Transform\Rector\MethodCall\MethodCallToAnotherMethodCallWithArgumentsRector::class)->configure([new \Rector\Transform\ValueObject\MethodCallToAnotherMethodCallWithArguments('Cake\\Database\\DriverInterface', 'supportsQuoting', 'supports', ['quote']), new \Rector\Transform\ValueObject\MethodCallToAnotherMethodCallWithArguments('Cake\\Database\\DriverInterface', 'supportsSavepoints', 'supports', ['savepoint'])]);
};
