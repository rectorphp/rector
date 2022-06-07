<?php

declare (strict_types=1);
namespace RectorPrefix20220607;

use Rector\CakePHP\Rector\MethodCall\ModalToGetSetRector;
use Rector\CakePHP\ValueObject\ModalToGetSet;
use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, ['RectorPrefix20220607\\Cake\\Routing\\Exception\\RedirectException' => 'RectorPrefix20220607\\Cake\\Http\\Exception\\RedirectException', 'RectorPrefix20220607\\Cake\\Database\\Expression\\Comparison' => 'RectorPrefix20220607\\Cake\\Database\\Expression\\ComparisonExpression']);
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [new MethodCallRename('RectorPrefix20220607\\Cake\\Database\\Schema\\TableSchema', 'getPrimary', 'getPrimaryKey'), new MethodCallRename('RectorPrefix20220607\\Cake\\Database\\Type\\DateTimeType', 'setTimezone', 'setDatabaseTimezone'), new MethodCallRename('RectorPrefix20220607\\Cake\\Database\\Expression\\QueryExpression', 'or_', 'or'), new MethodCallRename('RectorPrefix20220607\\Cake\\Database\\Expression\\QueryExpression', 'and_', 'and'), new MethodCallRename('RectorPrefix20220607\\Cake\\View\\Form\\ContextInterface', 'primaryKey', 'getPrimaryKey'), new MethodCallRename('RectorPrefix20220607\\Cake\\Http\\Middleware\\CsrfProtectionMiddleware', 'whitelistCallback', 'skipCheckCallback')]);
    $rectorConfig->ruleWithConfiguration(ModalToGetSetRector::class, [new ModalToGetSet('RectorPrefix20220607\\Cake\\Form\\Form', 'schema')]);
};
