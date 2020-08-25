<?php

declare(strict_types=1);

use function Rector\SymfonyPhpConfig\inline_value_objects;
use Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector;
use Rector\TypeDeclaration\ValueObject\ParameterTypehint;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    # scalar type hints, see https://github.com/nette/component-model/commit/f69df2ca224cad7b07f1c8835679393263ea6771
    # scalar param types https://github.com/nette/security/commit/84024f612fb3f55f5d6e3e3e28eef1ad0388fa56
    $services->set(AddParamTypeDeclarationRector::class)
        ->call('configure', [[
            AddParamTypeDeclarationRector::PARAMETER_TYPEHINTS => inline_value_objects([
                new ParameterTypehint('Nette\ComponentModel\Component', 'lookup', 0, '?string'),
                new ParameterTypehint('Nette\ComponentModel\Component', 'lookup', 1, 'bool'),
                new ParameterTypehint('Nette\ComponentModel\Component', 'lookupPath', 0, 'string'),
                new ParameterTypehint('Nette\ComponentModel\Component', 'lookupPath', 1, 'bool'),
                new ParameterTypehint('Nette\ComponentModel\Component', 'monitor', 0, 'string'),
                new ParameterTypehint('Nette\ComponentModel\Component', 'unmonitor', 0, 'string'),
                new ParameterTypehint(
                    'Nette\ComponentModel\Component',
                    'attached',
                    0,
                    'Nette\ComponentModel\IComponent'
                ),
                new ParameterTypehint(
                    'Nette\ComponentModel\Component',
                    'detached',
                    0,
                    'Nette\ComponentModel\IComponent'
                ),
                new ParameterTypehint('Nette\ComponentModel\Component', 'link', 0, 'string'),
                new ParameterTypehint('Nette\ComponentModel\Container', 'getComponent', 1, 'bool'),
                new ParameterTypehint('Nette\ComponentModel\Container', 'createComponent', 0, 'string'),
                new ParameterTypehint(
                    'Nette\ComponentModel\IComponent',
                    'setParent',
                    0,
                    '?Nette\ComponentModel\IContainer'
                ),
                new ParameterTypehint('Nette\ComponentModel\IComponent', 'setParent', 1, 'string'),
                new ParameterTypehint('Nette\ComponentModel\IContainer', 'getComponents', 0, 'bool'),
                new ParameterTypehint('Nette\Bridges\SecurityDI\SecurityExtension', '__construct', 0, 'bool'),
                new ParameterTypehint('Nette\Security\IUserStorage', 'setAuthenticated', 0, 'bool'),
                new ParameterTypehint('Nette\Security\IUserStorage', 'setIdentity', 0, '?Nette\Security\IIdentity'),
                new ParameterTypehint('Nette\Security\IUserStorage', 'setExpiration', 1, 'int'),
                new ParameterTypehint('Nette\Security\Identity', '__construct', 2, 'iterable'),
                new ParameterTypehint('Nette\Security\Identity', '__set', 0, 'string'),
                new ParameterTypehint('Nette\Security\Identity', '&__get', 0, 'string'),
                new ParameterTypehint('Nette\Security\Identity', '__isset', 0, 'string'),
                new ParameterTypehint('Nette\Security\Passwords', 'hash', 0, 'string'),
                new ParameterTypehint('Nette\Security\Passwords', 'verify', 0, 'string'),
                new ParameterTypehint('Nette\Security\Passwords', 'verify', 1, 'string'),
                new ParameterTypehint('Nette\Security\Passwords', 'needsRehash', 0, 'string'),
                new ParameterTypehint('Nette\Security\Permission', 'addRole', 0, 'string'),
                new ParameterTypehint('Nette\Security\Permission', 'hasRole', 0, 'string'),
                new ParameterTypehint('Nette\Security\Permission', 'getRoleParents', 0, 'string'),
                new ParameterTypehint('Nette\Security\Permission', 'removeRole', 0, 'string'),
                new ParameterTypehint('Nette\Security\Permission', 'addResource', 0, 'string'),
                new ParameterTypehint('Nette\Security\Permission', 'addResource', 1, 'string'),
                new ParameterTypehint('Nette\Security\Permission', 'hasResource', 0, 'string'),
                new ParameterTypehint('Nette\Security\Permission', 'resourceInheritsFrom', 0, 'string'),
                new ParameterTypehint('Nette\Security\Permission', 'resourceInheritsFrom', 1, 'string'),
                new ParameterTypehint('Nette\Security\Permission', 'resourceInheritsFrom', 2, 'bool'),
                new ParameterTypehint('Nette\Security\Permission', 'removeResource', 0, 'string'),
                new ParameterTypehint('Nette\Security\Permission', 'allow', 3, 'callable'),
                new ParameterTypehint('Nette\Security\Permission', 'deny', 3, 'callable'),
                new ParameterTypehint('Nette\Security\Permission', 'setRule', 0, 'bool'),
                new ParameterTypehint('Nette\Security\Permission', 'setRule', 1, 'bool'),
                new ParameterTypehint('Nette\Security\Permission', 'setRule', 5, 'callable'),
                new ParameterTypehint('Nette\Security\User', 'logout', 0, 'bool'),
                new ParameterTypehint('Nette\Security\User', 'getAuthenticator', 0, 'bool'),
                new ParameterTypehint('Nette\Security\User', 'isInRole', 0, 'string'),
                new ParameterTypehint('Nette\Security\User', 'getAuthorizator', 0, 'bool'),
                new ParameterTypehint('Nette\Security\User', 'getAuthorizator', 1, 'string'),
            ]),
        ]]);
};
