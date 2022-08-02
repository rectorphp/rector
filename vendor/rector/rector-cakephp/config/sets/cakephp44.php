<?php

declare (strict_types=1);
namespace RectorPrefix202208;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
# @see https://book.cakephp.org/4/en/appendices/4-4-migration-guide.html
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, ['Cake\\TestSuite\\ConsoleIntegrationTestTrait' => 'Cake\\Console\\TestSuite\\ConsoleIntegrationTestTrait', 'Cake\\TestSuite\\Stub\\ConsoleInput' => 'Cake\\Console\\TestSuite\\StubConsoleInput', 'Cake\\TestSuite\\Stub\\ConsoleOutput' => 'Cake\\Console\\TestSuite\\StubConsoleOutput', 'Cake\\TestSuite\\Stub\\MissingConsoleInputException' => 'Cake\\Console\\TestSuite\\MissingConsoleInputException', 'Cake\\TestSuite\\HttpClientTrait' => 'Cake\\Http\\TestSuite\\HttpClientTrait']);
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [new MethodCallRename('Cake\\Database\\Query', 'newExpr', 'expr')]);
};
