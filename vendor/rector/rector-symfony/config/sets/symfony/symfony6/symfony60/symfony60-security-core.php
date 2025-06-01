<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [
        new MethodCallRename('Symfony\\Component\\Security\\Core\\User\\UserProviderInterface', 'loadUserByUsername', 'loadUserByIdentifier'),
        // @see https://github.com/rectorphp/rector-symfony/issues/112
        new MethodCallRename('Symfony\\Component\\Security\\Core\\User\\UserInterface', 'getUsername', 'getUserIdentifier'),
    ]);
    $rectorConfig->ruleWithConfiguration(AddReturnTypeDeclarationRector::class, [
        // @see https://wouterj.nl/2021/09/symfony-6-native-typing#when-upgrading-to-symfony-54
        new AddReturnTypeDeclaration('Symfony\\Component\\Security\\Core\\User\\UserInterface', 'getRoles', new ArrayType(new MixedType(), new MixedType())),
        new AddReturnTypeDeclaration('Symfony\\Component\\Security\\Core\\Authentication\\RememberMe\\TokenProviderInterface', 'loadTokenBySeries', new ObjectType('Symfony\\Component\\Security\\Core\\Authentication\\RememberMe\\PersistentTokenInterface')),
        new AddReturnTypeDeclaration('Symfony\\Component\\Security\\Core\\Authorization\\Voter\\VoterInterface', 'vote', new IntegerType()),
        new AddReturnTypeDeclaration('Symfony\\Component\\Security\\Core\\Exception\\AuthenticationException', 'getMessageKey', new StringType()),
        new AddReturnTypeDeclaration('Symfony\\Component\\Security\\Core\\User\\UserProviderInterface', 'refreshUser', new ObjectType('Symfony\\Component\\Security\\Core\\User\\UserInterface')),
        new AddReturnTypeDeclaration('Symfony\\Component\\Security\\Core\\User\\UserProviderInterface', 'supportsClass', new BooleanType()),
    ]);
};
