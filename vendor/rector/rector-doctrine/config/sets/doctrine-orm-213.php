<?php

declare (strict_types=1);
namespace RectorPrefix202312;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [
        // @see https://github.com/doctrine/orm/pull/9876
        new MethodCallRename('Doctrine\\ORM\\Event\\LifecycleEventArgs', 'getEntityManager', 'getObjectManager'),
        new MethodCallRename('Doctrine\\ORM\\Event\\OnClearEventArgs', 'getEntityManager', 'getObjectManager'),
        new MethodCallRename('Doctrine\\ORM\\Event\\OnFlushEventArgs', 'getEntityManager', 'getObjectManager'),
        new MethodCallRename('Doctrine\\ORM\\Event\\PostFlushEventArgs', 'getEntityManager', 'getObjectManager'),
        new MethodCallRename('Doctrine\\ORM\\Event\\PreFlushEventArgs', 'getEntityManager', 'getObjectManager'),
        // @see https://github.com/doctrine/orm/pull/9906
        new MethodCallRename('Doctrine\\ORM\\Event\\LifecycleEventArgs', 'getEntity', 'getObject'),
    ]);
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        // @see https://github.com/doctrine/orm/pull/9906
        'Doctrine\\ORM\\Event\\LifecycleEventArgs' => 'Doctrine\\Persistence\\Event\\LifecycleEventArgs',
    ]);
};
