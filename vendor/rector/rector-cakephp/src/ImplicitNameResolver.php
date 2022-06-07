<?php

declare (strict_types=1);
namespace Rector\CakePHP;

/**
 * @inspired https://github.com/cakephp/upgrade/blob/756410c8b7d5aff9daec3fa1fe750a3858d422ac/src/Shell/Task/AppUsesTask.php
 */
final class ImplicitNameResolver
{
    /**
     * A map of old => new for use statements that are missing
     *
     * @var string[]
     */
    private const IMPLICIT_MAP = [
        'App' => 'RectorPrefix20220607\\Cake\\Core\\App',
        'AppController' => 'RectorPrefix20220607\\App\\Controller\\AppController',
        'AppHelper' => 'RectorPrefix20220607\\App\\View\\Helper\\AppHelper',
        'AppModel' => 'RectorPrefix20220607\\App\\Model\\AppModel',
        'Cache' => 'RectorPrefix20220607\\Cake\\Cache\\Cache',
        'CakeEventListener' => 'RectorPrefix20220607\\Cake\\Event\\EventListener',
        'CakeLog' => 'RectorPrefix20220607\\Cake\\Log\\Log',
        'CakePlugin' => 'RectorPrefix20220607\\Cake\\Core\\Plugin',
        'CakeTestCase' => 'RectorPrefix20220607\\Cake\\TestSuite\\TestCase',
        'CakeTestFixture' => 'RectorPrefix20220607\\Cake\\TestSuite\\Fixture\\TestFixture',
        'Component' => 'RectorPrefix20220607\\Cake\\Controller\\Component',
        'ComponentRegistry' => 'RectorPrefix20220607\\Cake\\Controller\\ComponentRegistry',
        'Configure' => 'RectorPrefix20220607\\Cake\\Core\\Configure',
        'ConnectionManager' => 'RectorPrefix20220607\\Cake\\Database\\ConnectionManager',
        'Controller' => 'RectorPrefix20220607\\Cake\\Controller\\Controller',
        'Debugger' => 'RectorPrefix20220607\\Cake\\Error\\Debugger',
        'ExceptionRenderer' => 'RectorPrefix20220607\\Cake\\Error\\ExceptionRenderer',
        'Helper' => 'RectorPrefix20220607\\Cake\\View\\Helper',
        'HelperRegistry' => 'RectorPrefix20220607\\Cake\\View\\HelperRegistry',
        'Inflector' => 'RectorPrefix20220607\\Cake\\Utility\\Inflector',
        'Model' => 'RectorPrefix20220607\\Cake\\Model\\Model',
        'ModelBehavior' => 'RectorPrefix20220607\\Cake\\Model\\Behavior',
        'Object' => 'RectorPrefix20220607\\Cake\\Core\\Object',
        'Router' => 'RectorPrefix20220607\\Cake\\Routing\\Router',
        'Shell' => 'RectorPrefix20220607\\Cake\\Console\\Shell',
        'View' => 'RectorPrefix20220607\\Cake\\View\\View',
        // Also apply to already renamed ones
        'Log' => 'RectorPrefix20220607\\Cake\\Log\\Log',
        'Plugin' => 'RectorPrefix20220607\\Cake\\Core\\Plugin',
        'TestCase' => 'RectorPrefix20220607\\Cake\\TestSuite\\TestCase',
        'TestFixture' => 'RectorPrefix20220607\\Cake\\TestSuite\\Fixture\\TestFixture',
    ];
    /**
     * This value used to be directory So "/" in path should be "\" in namespace
     */
    public function resolve(string $shortClass) : ?string
    {
        return self::IMPLICIT_MAP[$shortClass] ?? null;
    }
}
