<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [new MethodCallRename('Symfony\\Component\\Security\\Core\\Authentication\\Token\\TokenInterface', 'getUsername', 'getUserIdentifier'), new MethodCallRename('Symfony\\Component\\Security\\Core\\Exception\\UsernameNotFoundException', 'getUsername', 'getUserIdentifier'), new MethodCallRename('Symfony\\Component\\Security\\Core\\Exception\\UsernameNotFoundException', 'setUsername', 'setUserIdentifier'), new MethodCallRename('Symfony\\Component\\Security\\Core\\Authentication\\RememberMe\\PersistentTokenInterface', 'getUsername', 'getUserIdentifier')]);
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        'Symfony\\Component\\Security\\Core\\Exception\\UsernameNotFoundException' => 'Symfony\\Component\\Security\\Core\\Exception\\UserNotFoundException',
        // @see https://github.com/symfony/symfony/pull/39802
        'Symfony\\Component\\Security\\Core\\Encoder\\EncoderFactoryInterface' => 'Symfony\\Component\\PasswordHasher\\Hasher\\PasswordHasherFactoryInterface',
        'Symfony\\Component\\Security\\Core\\Encoder\\MessageDigestPasswordEncoder' => 'Symfony\\Component\\PasswordHasher\\Hasher\\MessageDigestPasswordHasher',
        'Symfony\\Component\\Security\\Core\\Encoder\\MigratingPasswordEncoder' => 'Symfony\\Component\\PasswordHasher\\Hasher\\MigratingPasswordHasher',
        'Symfony\\Component\\Security\\Core\\Encoder\\NativePasswordEncoder' => 'Symfony\\Component\\PasswordHasher\\Hasher\\NativePasswordHasher',
        'Symfony\\Component\\Security\\Core\\Encoder\\PasswordEncoderInterface' => 'Symfony\\Component\\PasswordHasher\\PasswordHasherInterface',
        'Symfony\\Component\\Security\\Core\\Encoder\\Pbkdf2PasswordEncoder' => 'Symfony\\Component\\PasswordHasher\\Hasher\\Pbkdf2PasswordHasher',
        'Symfony\\Component\\Security\\Core\\Encoder\\PlaintextPasswordEncoder' => 'Symfony\\Component\\PasswordHasher\\Hasher\\PlaintextPasswordHasher',
        'Symfony\\Component\\Security\\Core\\Encoder\\SelfSaltingEncoderInterface' => 'Symfony\\Component\\PasswordHasher\\LegacyPasswordHasherInterface',
        'Symfony\\Component\\Security\\Core\\Encoder\\SodiumPasswordEncoder' => 'Symfony\\Component\\PasswordHasher\\Hasher\\SodiumPasswordHasher',
        'Symfony\\Component\\Security\\Core\\Encoder\\UserPasswordEncoder' => 'Symfony\\Component\\PasswordHasher\\Hasher\\UserPasswordHasher',
        'Symfony\\Component\\Security\\Core\\Encoder\\UserPasswordEncoderInterface' => 'Symfony\\Component\\PasswordHasher\\Hasher\\UserPasswordHasherInterface',
    ]);
};
