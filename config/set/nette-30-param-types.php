<?php

declare(strict_types=1);

use PHPStan\Type\BooleanType;
use PHPStan\Type\CallableType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\IterableType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use PHPStan\Type\UnionType;
use function Rector\SymfonyPhpConfig\inline_value_objects;
use Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector;
use Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    # scalar type hints, see https://github.com/nette/component-model/commit/f69df2ca224cad7b07f1c8835679393263ea6771
    # scalar param types https://github.com/nette/security/commit/84024f612fb3f55f5d6e3e3e28eef1ad0388fa56

    $iterableType = new IterableType(new MixedType(), new MixedType());

    $services->set(AddParamTypeDeclarationRector::class)
        ->call('configure', [[
            AddParamTypeDeclarationRector::PARAMETER_TYPEHINTS => inline_value_objects([
                new AddParamTypeDeclaration(
                    'Nette\ComponentModel\Component',
                    'lookup',
                    0,
                    new UnionType([new StringType(), new NullType()])
                ),
                new AddParamTypeDeclaration('Nette\ComponentModel\Component', 'lookup', 1, new BooleanType()),
                new AddParamTypeDeclaration('Nette\ComponentModel\Component', 'lookupPath', 0, new StringType()),
                new AddParamTypeDeclaration('Nette\ComponentModel\Component', 'lookupPath', 1, new BooleanType()),
                new AddParamTypeDeclaration('Nette\ComponentModel\Component', 'monitor', 0, new StringType()),
                new AddParamTypeDeclaration('Nette\ComponentModel\Component', 'unmonitor', 0, new StringType()),
                new AddParamTypeDeclaration(
                    'Nette\ComponentModel\Component',
                    'attached',
                    0,
                    new ObjectType('Nette\ComponentModel\IComponent')
                ),
                new AddParamTypeDeclaration(
                    'Nette\ComponentModel\Component',
                    'detached',
                    0,
                    new ObjectType('Nette\ComponentModel\IComponent')
                ),
                new AddParamTypeDeclaration('Nette\ComponentModel\Component', 'link', 0, new StringType()),
                new AddParamTypeDeclaration('Nette\ComponentModel\Container', 'getComponent', 1, new BooleanType()),
                new AddParamTypeDeclaration('Nette\ComponentModel\Container', 'createComponent', 0, new StringType()),
                new AddParamTypeDeclaration(
                    'Nette\ComponentModel\IComponent',
                    'setParent',
                    0,
                    new UnionType([new ObjectType('Nette\ComponentModel\IContainer'), new NullType()])
                ),
                new AddParamTypeDeclaration('Nette\ComponentModel\IComponent', 'setParent', 1, new StringType()),
                new AddParamTypeDeclaration('Nette\ComponentModel\IContainer', 'getComponents', 0, new BooleanType()),
                new AddParamTypeDeclaration(
                    'Nette\Bridges\SecurityDI\SecurityExtension',
                    '__construct',
                    0,
                    new BooleanType()
                ),
                new AddParamTypeDeclaration('Nette\Security\IUserStorage', 'setAuthenticated', 0, new BooleanType()),
                new AddParamTypeDeclaration(
                    'Nette\Security\IUserStorage',
                    'setIdentity',
                    0,
                    new UnionType([new ObjectType('Nette\Security\IIdentity'), new NullType()])
                ),
                new AddParamTypeDeclaration('Nette\Security\IUserStorage', 'setExpiration', 1, new IntegerType()),
                new AddParamTypeDeclaration('Nette\Security\Identity', '__construct', 2, $iterableType),
                new AddParamTypeDeclaration('Nette\Security\Identity', '__set', 0, new StringType()),
                new AddParamTypeDeclaration('Nette\Security\Identity', '&__get', 0, new StringType()),
                new AddParamTypeDeclaration('Nette\Security\Identity', '__isset', 0, new StringType()),
                new AddParamTypeDeclaration('Nette\Security\Passwords', 'hash', 0, new StringType()),
                new AddParamTypeDeclaration('Nette\Security\Passwords', 'verify', 0, new StringType()),
                new AddParamTypeDeclaration('Nette\Security\Passwords', 'verify', 1, new StringType()),
                new AddParamTypeDeclaration('Nette\Security\Passwords', 'needsRehash', 0, new StringType()),
                new AddParamTypeDeclaration('Nette\Security\Permission', 'addRole', 0, new StringType()),
                new AddParamTypeDeclaration('Nette\Security\Permission', 'hasRole', 0, new StringType()),
                new AddParamTypeDeclaration('Nette\Security\Permission', 'getRoleParents', 0, new StringType()),
                new AddParamTypeDeclaration('Nette\Security\Permission', 'removeRole', 0, new StringType()),
                new AddParamTypeDeclaration('Nette\Security\Permission', 'addResource', 0, new StringType()),
                new AddParamTypeDeclaration('Nette\Security\Permission', 'addResource', 1, new StringType()),
                new AddParamTypeDeclaration('Nette\Security\Permission', 'hasResource', 0, new StringType()),
                new AddParamTypeDeclaration('Nette\Security\Permission', 'resourceInheritsFrom', 0, new StringType()),
                new AddParamTypeDeclaration('Nette\Security\Permission', 'resourceInheritsFrom', 1, new StringType()),
                new AddParamTypeDeclaration('Nette\Security\Permission', 'resourceInheritsFrom', 2, new BooleanType()),
                new AddParamTypeDeclaration('Nette\Security\Permission', 'removeResource', 0, new StringType()),
                new AddParamTypeDeclaration('Nette\Security\Permission', 'allow', 3, new CallableType()),
                new AddParamTypeDeclaration('Nette\Security\Permission', 'deny', 3, new CallableType()),
                new AddParamTypeDeclaration('Nette\Security\Permission', 'setRule', 0, new BooleanType()),
                new AddParamTypeDeclaration('Nette\Security\Permission', 'setRule', 1, new BooleanType()),
                new AddParamTypeDeclaration('Nette\Security\Permission', 'setRule', 5, new CallableType()),
                new AddParamTypeDeclaration('Nette\Security\User', 'logout', 0, new BooleanType()),
                new AddParamTypeDeclaration('Nette\Security\User', 'getAuthenticator', 0, new BooleanType()),
                new AddParamTypeDeclaration('Nette\Security\User', 'isInRole', 0, new StringType()),
                new AddParamTypeDeclaration('Nette\Security\User', 'getAuthorizator', 0, new BooleanType()),
                new AddParamTypeDeclaration('Nette\Security\User', 'getAuthorizator', 1, new StringType()),
            ]),
        ]]);
};
