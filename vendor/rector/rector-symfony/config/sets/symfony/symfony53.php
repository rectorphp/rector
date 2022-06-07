<?php

declare (strict_types=1);
namespace RectorPrefix20220607;

use PHPStan\Type\StringType;
use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\ClassConstFetch\RenameClassConstFetchRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Renaming\ValueObject\RenameClassConstFetch;
use Rector\Symfony\Set\SymfonySetList;
use Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration;
# https://github.com/symfony/symfony/blob/5.4/UPGRADE-5.3.md
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->sets([SymfonySetList::ANNOTATIONS_TO_ATTRIBUTES]);
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [
        // @see https://github.com/symfony/symfony/pull/40536
        new MethodCallRename('RectorPrefix20220607\\Symfony\\Component\\HttpFoundation\\RequestStack', 'getMasterRequest', 'getMainRequest'),
        new MethodCallRename('RectorPrefix20220607\\Symfony\\Component\\Console\\Helper\\Helper', 'strlen', 'width'),
        new MethodCallRename('RectorPrefix20220607\\Symfony\\Component\\Console\\Helper\\Helper', 'strlenWithoutDecoration', 'removeDecoration'),
        new MethodCallRename('RectorPrefix20220607\\Symfony\\Component\\HttpKernel\\Event\\KernelEvent', 'isMasterRequest', 'isMainRequest'),
        new MethodCallRename('RectorPrefix20220607\\Symfony\\Component\\Security\\Core\\Authentication\\Token\\TokenInterface', 'getUsername', 'getUserIdentifier'),
        new MethodCallRename('RectorPrefix20220607\\Symfony\\Component\\Security\\Core\\Exception\\UsernameNotFoundException', 'getUsername', 'getUserIdentifier'),
        new MethodCallRename('RectorPrefix20220607\\Symfony\\Component\\Security\\Core\\Exception\\UsernameNotFoundException', 'setUsername', 'setUserIdentifier'),
        new MethodCallRename('RectorPrefix20220607\\Symfony\\Component\\Security\\Core\\Authentication\\RememberMe\\PersistentTokenInterface', 'getUsername', 'getUserIdentifier'),
    ]);
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        'RectorPrefix20220607\\Symfony\\Component\\Security\\Core\\Exception\\UsernameNotFoundException' => 'RectorPrefix20220607\\Symfony\\Component\\Security\\Core\\Exception\\UserNotFoundException',
        // @see https://github.com/symfony/symfony/pull/39802
        'RectorPrefix20220607\\Symfony\\Component\\Security\\Core\\Encoder\\EncoderFactoryInterface' => 'RectorPrefix20220607\\Symfony\\Component\\PasswordHasher\\Hasher\\PasswordHasherFactoryInterface',
        'RectorPrefix20220607\\Symfony\\Component\\Security\\Core\\Encoder\\MessageDigestPasswordEncoder' => 'RectorPrefix20220607\\Symfony\\Component\\PasswordHasher\\Hasher\\MessageDigestPasswordHasher',
        'RectorPrefix20220607\\Symfony\\Component\\Security\\Core\\Encoder\\MigratingPasswordEncoder' => 'RectorPrefix20220607\\Symfony\\Component\\PasswordHasher\\Hasher\\MigratingPasswordHasher',
        'RectorPrefix20220607\\Symfony\\Component\\Security\\Core\\Encoder\\NativePasswordEncoder' => 'RectorPrefix20220607\\Symfony\\Component\\PasswordHasher\\Hasher\\NativePasswordHasher',
        'RectorPrefix20220607\\Symfony\\Component\\Security\\Core\\Encoder\\PasswordEncoderInterface' => 'RectorPrefix20220607\\Symfony\\Component\\PasswordHasher\\PasswordHasherInterface',
        'RectorPrefix20220607\\Symfony\\Component\\Security\\Core\\Encoder\\Pbkdf2PasswordEncoder' => 'RectorPrefix20220607\\Symfony\\Component\\PasswordHasher\\Hasher\\Pbkdf2PasswordHasher',
        'RectorPrefix20220607\\Symfony\\Component\\Security\\Core\\Encoder\\PlaintextPasswordEncoder' => 'RectorPrefix20220607\\Symfony\\Component\\PasswordHasher\\Hasher\\PlaintextPasswordHasher',
        'RectorPrefix20220607\\Symfony\\Component\\Security\\Core\\Encoder\\SelfSaltingEncoderInterface' => 'RectorPrefix20220607\\Symfony\\Component\\PasswordHasher\\LegacyPasswordHasherInterface',
        'RectorPrefix20220607\\Symfony\\Component\\Security\\Core\\Encoder\\SodiumPasswordEncoder' => 'RectorPrefix20220607\\Symfony\\Component\\PasswordHasher\\Hasher\\SodiumPasswordHasher',
        'RectorPrefix20220607\\Symfony\\Component\\Security\\Core\\Encoder\\UserPasswordEncoder' => 'RectorPrefix20220607\\Symfony\\Component\\PasswordHasher\\Hasher\\UserPasswordHasher',
        'RectorPrefix20220607\\Symfony\\Component\\Security\\Core\\Encoder\\UserPasswordEncoderInterface' => 'RectorPrefix20220607\\Symfony\\Component\\PasswordHasher\\Hasher\\UserPasswordHasherInterface',
    ]);
    $rectorConfig->ruleWithConfiguration(AddReturnTypeDeclarationRector::class, [new AddReturnTypeDeclaration('RectorPrefix20220607\\Symfony\\Component\\Mailer\\Transport\\AbstractTransportFactory', 'getEndpoint', new StringType())]);
    // rename constant
    $rectorConfig->ruleWithConfiguration(RenameClassConstFetchRector::class, [
        // @see https://github.com/symfony/symfony/pull/40536
        new RenameClassConstFetch('RectorPrefix20220607\\Symfony\\Component\\HttpKernel\\HttpKernelInterface', 'MASTER_REQUEST', 'MAIN_REQUEST'),
    ]);
};
