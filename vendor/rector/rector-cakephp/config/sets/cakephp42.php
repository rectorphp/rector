<?php

declare (strict_types=1);
namespace RectorPrefix20220607;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
# source: https://book.cakephp.org/4/en/appendices/4-2-migration-guide.html
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, ['RectorPrefix20220607\\Cake\\Core\\Exception\\Exception' => 'RectorPrefix20220607\\Cake\\Core\\Exception\\CakeException', 'RectorPrefix20220607\\Cake\\Database\\Exception' => 'RectorPrefix20220607\\Cake\\Database\\Exception\\DatabaseException']);
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [new MethodCallRename('RectorPrefix20220607\\Cake\\ORM\\Behavior', 'getTable', 'table')]);
};
