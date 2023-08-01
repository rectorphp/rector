<?php

declare (strict_types=1);
namespace RectorPrefix202308;

use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\ClassConstFetch\RenameClassConstFetchRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Renaming\ValueObject\RenameClassConstFetch;
use Rector\Symfony\Set\SymfonySetList;
use Rector\Symfony\Symfony53\Rector\Class_\CommandDescriptionToPropertyRector;
use Rector\Symfony\Symfony53\Rector\MethodCall\SwiftCreateMessageToNewEmailRector;
use Rector\Symfony\Symfony53\Rector\MethodCall\SwiftSetBodyToHtmlPlainMethodCallRector;
use Rector\Symfony\Symfony53\Rector\StaticPropertyFetch\KernelTestCaseContainerPropertyDeprecationRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration;
use Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration;
# https://github.com/symfony/symfony/blob/5.4/UPGRADE-5.3.md
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->sets([SymfonySetList::ANNOTATIONS_TO_ATTRIBUTES]);
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [
        // @see https://github.com/symfony/symfony/pull/40536
        new MethodCallRename('Symfony\\Component\\HttpFoundation\\RequestStack', 'getMasterRequest', 'getMainRequest'),
        new MethodCallRename('Symfony\\Component\\Console\\Helper\\Helper', 'strlen', 'width'),
        new MethodCallRename('Symfony\\Component\\Console\\Helper\\Helper', 'strlenWithoutDecoration', 'removeDecoration'),
        new MethodCallRename('Symfony\\Component\\HttpKernel\\Event\\KernelEvent', 'isMasterRequest', 'isMainRequest'),
        new MethodCallRename('Symfony\\Component\\Security\\Core\\Authentication\\Token\\TokenInterface', 'getUsername', 'getUserIdentifier'),
        new MethodCallRename('Symfony\\Component\\Security\\Core\\Exception\\UsernameNotFoundException', 'getUsername', 'getUserIdentifier'),
        new MethodCallRename('Symfony\\Component\\Security\\Core\\Exception\\UsernameNotFoundException', 'setUsername', 'setUserIdentifier'),
        new MethodCallRename('Symfony\\Component\\Security\\Core\\Authentication\\RememberMe\\PersistentTokenInterface', 'getUsername', 'getUserIdentifier'),
    ]);
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
    $rectorConfig->ruleWithConfiguration(AddReturnTypeDeclarationRector::class, [new AddReturnTypeDeclaration('Symfony\\Component\\Mailer\\Transport\\AbstractTransportFactory', 'getEndpoint', new StringType())]);
    // rename constant
    $rectorConfig->ruleWithConfiguration(RenameClassConstFetchRector::class, [
        // @see https://github.com/symfony/symfony/pull/40536
        new RenameClassConstFetch('Symfony\\Component\\HttpKernel\\HttpKernelInterface', 'MASTER_REQUEST', 'MAIN_REQUEST'),
    ]);
    $rectorConfig->ruleWithConfiguration(AddParamTypeDeclarationRector::class, [
        // @see https://github.com/symfony/symfony/commit/ce77be2507631cd12e4ca37510dab37f4c2b759a
        new AddParamTypeDeclaration('Symfony\\Component\\Form\\DataMapperInterface', 'mapFormsToData', 0, new ObjectType(\Traversable::class)),
        // @see https://github.com/symfony/symfony/commit/ce77be2507631cd12e4ca37510dab37f4c2b759a
        new AddParamTypeDeclaration('Symfony\\Component\\Form\\DataMapperInterface', 'mapDataToForms', 1, new ObjectType(\Traversable::class)),
    ]);
    $rectorConfig->rules([
        KernelTestCaseContainerPropertyDeprecationRector::class,
        CommandDescriptionToPropertyRector::class,
        // @see https://symfony.com/blog/the-end-of-swiftmailer
        SwiftCreateMessageToNewEmailRector::class,
        SwiftSetBodyToHtmlPlainMethodCallRector::class,
    ]);
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        'Swift_Mailer' => 'Symfony\\Component\\Mailer\\MailerInterface',
        'Swift_Message' => 'Symfony\\Component\\Mime\\Email',
        // message
        'Swift_Mime_SimpleMessage' => 'Symfony\\Component\\Mime\\RawMessage',
        // transport
        'Swift_SmtpTransport' => 'Symfony\\Component\\Mailer\\Transport\\Smtp\\EsmtpTransport',
        'Swift_FailoverTransport' => 'Symfony\\Component\\Mailer\\Transport\\FailoverTransport',
        'Swift_SendmailTransport' => 'Symfony\\Component\\Mailer\\Transport\\SendmailTransport',
    ]);
};
