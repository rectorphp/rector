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
        'App' => 'Cake\\Core\\App',
        'AppController' => 'App\\Controller\\AppController',
        'AppHelper' => 'App\\View\\Helper\\AppHelper',
        'AppModel' => 'App\\Model\\AppModel',
        'Cache' => 'Cake\\Cache\\Cache',
        'CakeEventListener' => 'Cake\\Event\\EventListener',
        'CakeLog' => 'Cake\\Log\\Log',
        'CakePlugin' => 'Cake\\Core\\Plugin',
        'CakeTestCase' => 'Cake\\TestSuite\\TestCase',
        'CakeTestFixture' => 'Cake\\TestSuite\\Fixture\\TestFixture',
        'Component' => 'Cake\\Controller\\Component',
        'ComponentRegistry' => 'Cake\\Controller\\ComponentRegistry',
        'Configure' => 'Cake\\Core\\Configure',
        'ConnectionManager' => 'Cake\\Database\\ConnectionManager',
        'Controller' => 'Cake\\Controller\\Controller',
        'Debugger' => 'Cake\\Error\\Debugger',
        'ExceptionRenderer' => 'Cake\\Error\\ExceptionRenderer',
        'Helper' => 'Cake\\View\\Helper',
        'HelperRegistry' => 'Cake\\View\\HelperRegistry',
        'Inflector' => 'Cake\\Utility\\Inflector',
        'Model' => 'Cake\\Model\\Model',
        'ModelBehavior' => 'Cake\\Model\\Behavior',
        'Object' => 'Cake\\Core\\Object',
        'Router' => 'Cake\\Routing\\Router',
        'Shell' => 'Cake\\Console\\Shell',
        'View' => 'Cake\\View\\View',
        // Also apply to already renamed ones
        'Log' => 'Cake\\Log\\Log',
        'Plugin' => 'Cake\\Core\\Plugin',
        'TestCase' => 'Cake\\TestSuite\\TestCase',
        'TestFixture' => 'Cake\\TestSuite\\Fixture\\TestFixture',
    ];
    /**
     * This value used to be directory So "/" in path should be "\" in namespace
     */
    public function resolve(string $shortClass) : ?string
    {
        return self::IMPLICIT_MAP[$shortClass] ?? null;
    }
}
