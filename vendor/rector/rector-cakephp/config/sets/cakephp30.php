<?php

declare (strict_types=1);
namespace RectorPrefix20211020;

use Rector\CakePHP\Rector\Namespace_\AppUsesStaticCallToUseStatementRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    # @see https://github.com/cakephp/upgrade/tree/master/src/Shell/Task
    $services->set(\Rector\CakePHP\Rector\Namespace_\AppUsesStaticCallToUseStatementRector::class);
    $services->set(\Rector\Renaming\Rector\Name\RenameClassRector::class)->call('configure', [[\Rector\Renaming\Rector\Name\RenameClassRector::OLD_TO_NEW_CLASSES => [
        # see https://github.com/cakephp/upgrade/blob/756410c8b7d5aff9daec3fa1fe750a3858d422ac/src/Shell/Task/RenameClassesTask.php#L37
        'Cake\\Network\\Http\\HttpSocket' => 'Cake\\Network\\Http\\Client',
        'Cake\\Model\\ConnectionManager' => 'Cake\\Database\\ConnectionManager',
        'Cake\\TestSuite\\CakeTestCase' => 'Cake\\TestSuite\\TestCase',
        'Cake\\TestSuite\\Fixture\\CakeTestFixture' => 'Cake\\TestSuite\\Fixture\\TestFixture',
        'Cake\\Utility\\String' => 'Cake\\Utility\\Text',
        'CakePlugin' => 'Plugin',
        'CakeException' => 'Exception',
        # see https://book.cakephp.org/3/en/appendices/3-0-migration-guide.html#configure
        'Cake\\Configure\\PhpReader' => 'Cake\\Core\\Configure\\EnginePhpConfig',
        'Cake\\Configure\\IniReader' => 'Cake\\Core\\Configure\\EngineIniConfig',
        'Cake\\Configure\\ConfigReaderInterface' => 'Cake\\Core\\Configure\\ConfigEngineInterface',
        # https://book.cakephp.org/3/en/appendices/3-0-migration-guide.html#request
        'CakeRequest' => 'Cake\\Network\\Request',
    ]]]);
};
