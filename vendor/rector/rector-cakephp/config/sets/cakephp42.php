<?php

declare (strict_types=1);
namespace RectorPrefix20220418;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
# source: https://book.cakephp.org/4/en/appendices/4-2-migration-guide.html
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $services = $rectorConfig->services();
    $services->set(\Rector\Renaming\Rector\Name\RenameClassRector::class)->configure(['Cake\\Core\\Exception\\Exception' => 'Cake\\Core\\Exception\\CakeException', 'Cake\\Database\\Exception' => 'Cake\\Database\\Exception\\DatabaseException']);
    $services->set(\Rector\Renaming\Rector\MethodCall\RenameMethodRector::class)->configure([new \Rector\Renaming\ValueObject\MethodCallRename('Cake\\ORM\\Behavior', 'getTable', 'table')]);
};
