<?php

declare (strict_types=1);
namespace RectorPrefix20220607;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;
# @see https://book.cakephp.org/4/en/appendices/4-4-migration-guide.html
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, ['RectorPrefix20220607\\Cake\\TestSuite\\ConsoleIntegrationTestTrait' => 'RectorPrefix20220607\\Cake\\Console\\TestSuite\\ConsoleIntegrationTestTrait', 'RectorPrefix20220607\\Cake\\TestSuite\\Stub\\ConsoleInput' => 'RectorPrefix20220607\\Cake\\Console\\TestSuite\\StubConsoleInput', 'RectorPrefix20220607\\Cake\\TestSuite\\Stub\\ConsoleOutput' => 'RectorPrefix20220607\\Cake\\Console\\TestSuite\\StubConsoleOutput', 'RectorPrefix20220607\\Cake\\TestSuite\\Stub\\MissingConsoleInputException' => 'RectorPrefix20220607\\Cake\\Console\\TestSuite\\MissingConsoleInputException', 'RectorPrefix20220607\\Cake\\TestSuite\\HttpClientTrait' => 'RectorPrefix20220607\\Cake\\Http\\TestSuite\\HttpClientTrait']);
};
