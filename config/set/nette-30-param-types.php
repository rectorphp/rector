<?php

declare(strict_types=1);

use Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    # scalar type hints, see https://github.com/nette/component-model/commit/f69df2ca224cad7b07f1c8835679393263ea6771
    # scalar param types https://github.com/nette/security/commit/84024f612fb3f55f5d6e3e3e28eef1ad0388fa56
    $services->set(AddParamTypeDeclarationRector::class)
        ->call('configure', [[
            AddParamTypeDeclarationRector::TYPEHINT_FOR_PARAMETER_BY_METHOD_BY_CLASS => [
                'Nette\ComponentModel\Component' => [
                    'lookup' => ['?string', 'bool'],
                    'lookupPath' => ['string', 'bool'],
                    'monitor' => ['string'],
                    'unmonitor' => ['string'],
                    'attached' => ['Nette\ComponentModel\IComponent'],
                    'detached' => ['Nette\ComponentModel\IComponent'],
                    'link' => ['string'],
                ],
                'Nette\ComponentModel\Container' => [
                    'getComponent' => [
                        1 => 'bool',
                    ],
                    'createComponent' => ['string'],
                ],
                'Nette\ComponentModel\IComponent' => [
                    'setParent' => ['?Nette\ComponentModel\IContainer', 'string'],
                ],
                'Nette\ComponentModel\IContainer' => [
                    'getComponents' => ['bool'],
                ],
                'Nette\Bridges\SecurityDI\SecurityExtension' => [
                    '__construct' => ['bool'],
                ],
                'Nette\Security\IUserStorage' => [
                    'setAuthenticated' => ['bool'],
                    'setIdentity' => ['?Nette\Security\IIdentity'],
                    'setExpiration' => [
                        1 => 'int',
                    ],
                ],
                'Nette\Security\Identity' => [
                    '__construct' => [
                        2 => 'iterable',
                    ],
                    '__set' => ['string'],
                    '&__get' => ['string'],
                    '__isset' => ['string'],
                ],
                'Nette\Security\Passwords' => [
                    'hash' => ['string'],
                    'verify' => ['string', 'string'],
                    'needsRehash' => ['string'],
                ],
                'Nette\Security\Permission' => [
                    'addRole' => ['string'],
                    'hasRole' => ['string'],
                    'getRoleParents' => ['string'],
                    'removeRole' => ['string'],
                    'addResource' => ['string', 'string'],
                    'hasResource' => ['string'],
                    'resourceInheritsFrom' => ['string', 'string', 'bool'],
                    'removeResource' => ['string'],
                    'allow' => [
                        3 => 'callable',
                    ],
                    'deny' => [
                        3 => 'callable',
                    ],
                    'setRule' => [
                        'bool',
                        'bool',
                        5 => 'callable',
                    ],
                ],
                'Nette\Security\User' => [
                    'logout' => ['bool'],
                    'getAuthenticator' => ['bool'],
                    'isInRole' => ['string'],
                    'getAuthorizator' => ['bool', 'string'],
                ],
            ],
        ]]);
};
