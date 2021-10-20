<?php

declare (strict_types=1);
namespace RectorPrefix20211020;

use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Transform\Rector\Assign\PropertyFetchToMethodCallRector;
use Rector\Transform\Rector\MethodCall\MethodCallToAnotherMethodCallWithArgumentsRector;
use Rector\Transform\ValueObject\MethodCallToAnotherMethodCallWithArguments;
use Rector\Transform\ValueObject\PropertyFetchToMethodCall;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;
# source: https://book.cakephp.org/4.next/en/appendices/4-3-migration-guide.html
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\Renaming\Rector\MethodCall\RenameMethodRector::class)->call('configure', [[\Rector\Renaming\Rector\MethodCall\RenameMethodRector::METHOD_CALL_RENAMES => \Symplify\SymfonyPhpConfig\ValueObjectInliner::inline([new \Rector\Renaming\ValueObject\MethodCallRename('Cake\\Controller\\Component', 'shutdown', 'afterFilter')])]]);
    $services->set(\Rector\Transform\Rector\Assign\PropertyFetchToMethodCallRector::class)->call('configure', [[\Rector\Transform\Rector\Assign\PropertyFetchToMethodCallRector::PROPERTIES_TO_METHOD_CALLS => \Symplify\SymfonyPhpConfig\ValueObjectInliner::inline([new \Rector\Transform\ValueObject\PropertyFetchToMethodCall('Cake\\Network\\Socket', 'connected', 'isConnected'), new \Rector\Transform\ValueObject\PropertyFetchToMethodCall('Cake\\Network\\Socket', 'encrypted', 'isEncrypted'), new \Rector\Transform\ValueObject\PropertyFetchToMethodCall('Cake\\Network\\Socket', 'lastError', 'lastError')])]]);
    $services->set(\Rector\Transform\Rector\MethodCall\MethodCallToAnotherMethodCallWithArgumentsRector::class)->call('configure', [[\Rector\Transform\Rector\MethodCall\MethodCallToAnotherMethodCallWithArgumentsRector::METHOD_CALL_RENAMES_WITH_ADDED_ARGUMENTS => \Symplify\SymfonyPhpConfig\ValueObjectInliner::inline([new \Rector\Transform\ValueObject\MethodCallToAnotherMethodCallWithArguments('Cake\\Database\\DriverInterface', 'supportsQuoting', 'supports', ['quote']), new \Rector\Transform\ValueObject\MethodCallToAnotherMethodCallWithArguments('Cake\\Database\\DriverInterface', 'supportsSavepoints', 'supports', ['savepoint'])])]]);
};
