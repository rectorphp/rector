<?php

declare(strict_types=1);

use PHPStan\Type\ObjectType;
use Rector\Renaming\Rector\ClassConstFetch\RenameClassConstFetchRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\PropertyFetch\RenamePropertyRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Renaming\ValueObject\RenameClassAndConstFetch;
use Rector\Renaming\ValueObject\RenameProperty;
use Rector\Symfony5\Rector\MethodCall\DefinitionAliasSetPrivateToSetPublicRector;
use Rector\Symfony5\Rector\MethodCall\FormBuilderSetDataMapperRector;
use Rector\Symfony5\Rector\MethodCall\ReflectionExtractorEnableMagicCallExtractorRector;
use Rector\Symfony5\Rector\MethodCall\ValidatorBuilderEnableAnnotationMappingRector;
use Rector\Symfony5\Rector\New_\PropertyAccessorCreationBooleanToFlagsRector;
use Rector\Symfony5\Rector\New_\PropertyPathMapperToDataMapperRector;
use Rector\Symfony5\Rector\StaticCall\BinaryFileResponseCreateToNewInstanceRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector;
use Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

# https://github.com/symfony/symfony/blob/5.x/UPGRADE-5.2.md

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->import(__DIR__ . '/symfony50-types.php');

    $services = $containerConfigurator->services();

    # https://github.com/symfony/symfony/blob/5.x/UPGRADE-5.2.md#form
    $services->set(PropertyPathMapperToDataMapperRector::class);

    # https://github.com/symfony/symfony/blob/5.x/UPGRADE-5.2.md#httpfoundation
    $services->set(BinaryFileResponseCreateToNewInstanceRector::class);

    # https://github.com/symfony/symfony/blob/5.x/UPGRADE-5.2.md#mime
    $services->set(RenameMethodRector::class)
        ->call('configure', [[
            RenameMethodRector::METHOD_CALL_RENAMES => ValueObjectInliner::inline([
                new MethodCallRename('Symfony\Component\Mime\Address', 'fromString', 'create'),
            ]),
        ]]);

    # https://github.com/symfony/symfony/blob/5.x/UPGRADE-5.2.md#propertyaccess
    $services->set(PropertyAccessorCreationBooleanToFlagsRector::class);

    # https://github.com/symfony/symfony/blob/5.x/UPGRADE-5.2.md#propertyinfo
    $services->set(ReflectionExtractorEnableMagicCallExtractorRector::class);

    # https://github.com/symfony/symfony/blob/5.x/UPGRADE-5.2.md#security
    $services->set(RenameClassConstFetchRector::class)
        ->call('configure', [[
            RenameClassConstFetchRector::CLASS_CONSTANT_RENAME => ValueObjectInliner::inline([
                new RenameClassAndConstFetch(
                    'Symfony\Component\Security\Http\Firewall\AccessListener',
                    'PUBLIC_ACCESS',
                    'Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter',
                    'PUBLIC_ACCESS'
                ),
            ]),
        ]]);

    $services->set(RenameMethodRector::class)
        ->call('configure', [[
            RenameMethodRector::METHOD_CALL_RENAMES => ValueObjectInliner::inline([
                new MethodCallRename(
                    'Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken',
                    'setProviderKey',
                    'setFirewallName'
                ),
                new MethodCallRename(
                    'Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken',
                    'getProviderKey',
                    'getFirewallName'
                ),
                new MethodCallRename(
                    'Symfony\Component\Security\Core\Authentication\Token\RememberMeToken',
                    'setProviderKey',
                    'setFirewallName'
                ),
                new MethodCallRename(
                    'Symfony\Component\Security\Core\Authentication\Token\RememberMeToken',
                    'getProviderKey',
                    'getFirewallName'
                ),
                new MethodCallRename(
                    'Symfony\Component\Security\Core\Authentication\Token\SwitchUserToken',
                    'setProviderKey',
                    'setFirewallName'
                ),
                new MethodCallRename(
                    'Symfony\Component\Security\Core\Authentication\Token\SwitchUserToken',
                    'getProviderKey',
                    'getFirewallName'
                ),
                new MethodCallRename(
                    'Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken',
                    'setProviderKey',
                    'setFirewallName'
                ),
                new MethodCallRename(
                    'Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken',
                    'getProviderKey',
                    'getFirewallName'
                ),
                new MethodCallRename(
                    'Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler',
                    'setProviderKey',
                    'setFirewallName'
                ),
                new MethodCallRename(
                    'Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler',
                    'getProviderKey',
                    'getFirewallName'
                ),
            ]),
        ]]);

    # https://github.com/symfony/symfony/blob/5.x/UPGRADE-5.2.md#dependencyinjection
    $services->set(DefinitionAliasSetPrivateToSetPublicRector::class);

    # https://github.com/symfony/symfony/blob/5.x/UPGRADE-5.2.md#form
    $services->set(FormBuilderSetDataMapperRector::class);

    # https://github.com/symfony/symfony/blob/5.x/UPGRADE-5.2.md#validator
    $services->set(ValidatorBuilderEnableAnnotationMappingRector::class);

    # https://github.com/symfony/symfony/blob/5.x/UPGRADE-5.2.md#notifier
    $services->set(AddParamTypeDeclarationRector::class)
        ->call('configure', [[
            AddParamTypeDeclarationRector::PARAMETER_TYPEHINTS => ValueObjectInliner::inline([
                new AddParamTypeDeclaration(
                    'Symfony\Component\Notifier\NotifierInterface',
                    'send',
                    1,
                    new ObjectType('Symfony\Component\Notifier\Recipient\RecipientInterface'),
                ),
                new AddParamTypeDeclaration(
                    'Symfony\Component\Notifier\Notifier',
                    'getChannels',
                    1,
                    new ObjectType('Symfony\Component\Notifier\Recipient\RecipientInterface'),
                ),
                new AddParamTypeDeclaration(
                    'Symfony\Component\Notifier\Channel\ChannelInterface',
                    'notify',
                    1,
                    new ObjectType('Symfony\Component\Notifier\Recipient\RecipientInterface'),
                ),
                new AddParamTypeDeclaration(
                    'Symfony\Component\Notifier\Channel\ChannelInterface',
                    'supports',
                    1,
                    new ObjectType('Symfony\Component\Notifier\Recipient\RecipientInterface'),
                ),
            ]),
        ]]);

    # https://github.com/symfony/symfony/blob/5.x/UPGRADE-5.2.md#notifier
    $services->set(AddParamTypeDeclarationRector::class)
        ->call('configure', [[
            AddParamTypeDeclarationRector::PARAMETER_TYPEHINTS => ValueObjectInliner::inline([
                new AddParamTypeDeclaration(
                    'Symfony\Component\Notifier\Notification\ChatNotificationInterface',
                    'asChatMessage',
                    0,
                    new ObjectType('Symfony\Component\Notifier\Recipient\RecipientInterface'),
                ),
                new AddParamTypeDeclaration(
                    'Symfony\Component\Notifier\Notification\EmailNotificationInterface',
                    'asEmailMessage',
                    0,
                    new ObjectType('Symfony\Component\Notifier\Recipient\EmailRecipientInterface'),
                ),
                new AddParamTypeDeclaration(
                    'Symfony\Component\Notifier\Notification\SmsNotificationInterface',
                    'asSmsMessage',
                    0,
                    new ObjectType('Symfony\Component\Notifier\Recipient\SmsRecipientInterface'),
                ),
            ]),
        ]]);

    # https://github.com/symfony/symfony/blob/5.x/UPGRADE-5.2.md#security
    $services->set(RenamePropertyRector::class)
        ->call('configure', [[
            RenamePropertyRector::RENAMED_PROPERTIES => ValueObjectInliner::inline([
                new RenameProperty(
                    'Symfony\Component\Security\Http\RememberMe\AbstractRememberMeServices',
                    'providerKey',
                    'firewallName'
                ),
            ]),
        ]]);
};
