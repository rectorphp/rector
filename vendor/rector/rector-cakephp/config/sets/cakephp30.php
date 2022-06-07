<?php

declare (strict_types=1);
namespace RectorPrefix20220607;

use Rector\CakePHP\Rector\Namespace_\AppUsesStaticCallToUseStatementRector;
use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;
return static function (RectorConfig $rectorConfig) : void {
    # @see https://github.com/cakephp/upgrade/tree/master/src/Shell/Task
    $rectorConfig->rule(AppUsesStaticCallToUseStatementRector::class);
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        # see https://github.com/cakephp/upgrade/blob/756410c8b7d5aff9daec3fa1fe750a3858d422ac/src/Shell/Task/RenameClassesTask.php#L37
        'RectorPrefix20220607\\Cake\\Network\\Http\\HttpSocket' => 'RectorPrefix20220607\\Cake\\Network\\Http\\Client',
        'RectorPrefix20220607\\Cake\\Model\\ConnectionManager' => 'RectorPrefix20220607\\Cake\\Database\\ConnectionManager',
        'RectorPrefix20220607\\Cake\\TestSuite\\CakeTestCase' => 'RectorPrefix20220607\\Cake\\TestSuite\\TestCase',
        'RectorPrefix20220607\\Cake\\TestSuite\\Fixture\\CakeTestFixture' => 'RectorPrefix20220607\\Cake\\TestSuite\\Fixture\\TestFixture',
        'RectorPrefix20220607\\Cake\\Utility\\String' => 'RectorPrefix20220607\\Cake\\Utility\\Text',
        'CakePlugin' => 'Plugin',
        'CakeException' => 'Exception',
        # see https://book.cakephp.org/3/en/appendices/3-0-migration-guide.html#configure
        'RectorPrefix20220607\\Cake\\Configure\\PhpReader' => 'RectorPrefix20220607\\Cake\\Core\\Configure\\EnginePhpConfig',
        'RectorPrefix20220607\\Cake\\Configure\\IniReader' => 'RectorPrefix20220607\\Cake\\Core\\Configure\\EngineIniConfig',
        'RectorPrefix20220607\\Cake\\Configure\\ConfigReaderInterface' => 'RectorPrefix20220607\\Cake\\Core\\Configure\\ConfigEngineInterface',
        # https://book.cakephp.org/3/en/appendices/3-0-migration-guide.html#request
        'CakeRequest' => 'RectorPrefix20220607\\Cake\\Network\\Request',
    ]);
};
