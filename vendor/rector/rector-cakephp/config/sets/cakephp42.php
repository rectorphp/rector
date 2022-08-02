<?php

declare (strict_types=1);
namespace RectorPrefix202208;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
# source: https://book.cakephp.org/4/en/appendices/4-2-migration-guide.html
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, ['Cake\\Core\\Exception\\Exception' => 'Cake\\Core\\Exception\\CakeException', 'Cake\\Database\\Exception' => 'Cake\\Database\\Exception\\DatabaseException']);
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [new MethodCallRename('Cake\\ORM\\Behavior', 'getTable', 'table')]);
};
