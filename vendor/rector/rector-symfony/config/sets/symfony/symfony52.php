<?php

declare (strict_types=1);
namespace RectorPrefix202208;

use PHPStan\Type\ObjectType;
use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\ClassConstFetch\RenameClassConstFetchRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\PropertyFetch\RenamePropertyRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Renaming\ValueObject\RenameClassAndConstFetch;
use Rector\Renaming\ValueObject\RenameProperty;
use Rector\Symfony\Rector\MethodCall\DefinitionAliasSetPrivateToSetPublicRector;
use Rector\Symfony\Rector\MethodCall\FormBuilderSetDataMapperRector;
use Rector\Symfony\Rector\MethodCall\ReflectionExtractorEnableMagicCallExtractorRector;
use Rector\Symfony\Rector\MethodCall\ValidatorBuilderEnableAnnotationMappingRector;
use Rector\Symfony\Rector\New_\PropertyAccessorCreationBooleanToFlagsRector;
use Rector\Symfony\Rector\New_\PropertyPathMapperToDataMapperRector;
use Rector\Symfony\Rector\StaticCall\BinaryFileResponseCreateToNewInstanceRector;
use Rector\Symfony\Set\SymfonySetList;
use Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector;
use Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration;
# https://github.com/symfony/symfony/blob/5.x/UPGRADE-5.2.md
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->sets([SymfonySetList::ANNOTATIONS_TO_ATTRIBUTES]);
    # https://github.com/symfony/symfony/blob/5.x/UPGRADE-5.2.md#form
    $rectorConfig->rule(PropertyPathMapperToDataMapperRector::class);
    # https://github.com/symfony/symfony/blob/5.x/UPGRADE-5.2.md#httpfoundation
    $rectorConfig->rule(BinaryFileResponseCreateToNewInstanceRector::class);
    # https://github.com/symfony/symfony/blob/5.x/UPGRADE-5.2.md#propertyaccess
    $rectorConfig->rule(PropertyAccessorCreationBooleanToFlagsRector::class);
    # https://github.com/symfony/symfony/blob/5.x/UPGRADE-5.2.md#propertyinfo
    $rectorConfig->rule(ReflectionExtractorEnableMagicCallExtractorRector::class);
    # https://github.com/symfony/symfony/blob/5.x/UPGRADE-5.2.md#security
    $rectorConfig->ruleWithConfiguration(RenameClassConstFetchRector::class, [new RenameClassAndConstFetch('Symfony\\Component\\Security\\Http\\Firewall\\AccessListener', 'PUBLIC_ACCESS', 'Symfony\\Component\\Security\\Core\\Authorization\\Voter\\AuthenticatedVoter', 'PUBLIC_ACCESS')]);
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [
        # https://github.com/symfony/symfony/blob/5.x/UPGRADE-5.2.md#mime
        new MethodCallRename('Symfony\\Component\\Mime\\Address', 'fromString', 'create'),
        new MethodCallRename('Symfony\\Component\\Security\\Core\\Authentication\\Token\\PreAuthenticatedToken', 'setProviderKey', 'setFirewallName'),
        new MethodCallRename('Symfony\\Component\\Security\\Core\\Authentication\\Token\\PreAuthenticatedToken', 'getProviderKey', 'getFirewallName'),
        new MethodCallRename('Symfony\\Component\\Security\\Core\\Authentication\\Token\\RememberMeToken', 'setProviderKey', 'setFirewallName'),
        new MethodCallRename('Symfony\\Component\\Security\\Core\\Authentication\\Token\\RememberMeToken', 'getProviderKey', 'getFirewallName'),
        new MethodCallRename('Symfony\\Component\\Security\\Core\\Authentication\\Token\\SwitchUserToken', 'setProviderKey', 'setFirewallName'),
        new MethodCallRename('Symfony\\Component\\Security\\Core\\Authentication\\Token\\SwitchUserToken', 'getProviderKey', 'getFirewallName'),
        new MethodCallRename('Symfony\\Component\\Security\\Core\\Authentication\\Token\\UsernamePasswordToken', 'setProviderKey', 'setFirewallName'),
        new MethodCallRename('Symfony\\Component\\Security\\Core\\Authentication\\Token\\UsernamePasswordToken', 'getProviderKey', 'getFirewallName'),
        new MethodCallRename('Symfony\\Component\\Security\\Http\\Authentication\\DefaultAuthenticationSuccessHandler', 'setProviderKey', 'setFirewallName'),
        new MethodCallRename('Symfony\\Component\\Security\\Http\\Authentication\\DefaultAuthenticationSuccessHandler', 'getProviderKey', 'getFirewallName'),
    ]);
    # https://github.com/symfony/symfony/blob/5.x/UPGRADE-5.2.md#dependencyinjection
    $rectorConfig->rule(DefinitionAliasSetPrivateToSetPublicRector::class);
    # https://github.com/symfony/symfony/blob/5.x/UPGRADE-5.2.md#form
    $rectorConfig->rule(FormBuilderSetDataMapperRector::class);
    # https://github.com/symfony/symfony/blob/5.x/UPGRADE-5.2.md#validator
    $rectorConfig->rule(ValidatorBuilderEnableAnnotationMappingRector::class);
    # https://github.com/symfony/symfony/blob/5.x/UPGRADE-5.2.md#notifier
    $rectorConfig->ruleWithConfiguration(AddParamTypeDeclarationRector::class, [new AddParamTypeDeclaration('Symfony\\Component\\Notifier\\NotifierInterface', 'send', 1, new ObjectType('Symfony\\Component\\Notifier\\Recipient\\RecipientInterface')), new AddParamTypeDeclaration('Symfony\\Component\\Notifier\\Notifier', 'getChannels', 1, new ObjectType('Symfony\\Component\\Notifier\\Recipient\\RecipientInterface')), new AddParamTypeDeclaration('Symfony\\Component\\Notifier\\Channel\\ChannelInterface', 'notify', 1, new ObjectType('Symfony\\Component\\Notifier\\Recipient\\RecipientInterface')), new AddParamTypeDeclaration('Symfony\\Component\\Notifier\\Channel\\ChannelInterface', 'supports', 1, new ObjectType('Symfony\\Component\\Notifier\\Recipient\\RecipientInterface')), new AddParamTypeDeclaration('Symfony\\Component\\Notifier\\Notification\\ChatNotificationInterface', 'asChatMessage', 0, new ObjectType('Symfony\\Component\\Notifier\\Recipient\\RecipientInterface')), new AddParamTypeDeclaration('Symfony\\Component\\Notifier\\Notification\\EmailNotificationInterface', 'asEmailMessage', 0, new ObjectType('Symfony\\Component\\Notifier\\Recipient\\EmailRecipientInterface')), new AddParamTypeDeclaration('Symfony\\Component\\Notifier\\Notification\\SmsNotificationInterface', 'asSmsMessage', 0, new ObjectType('Symfony\\Component\\Notifier\\Recipient\\SmsRecipientInterface'))]);
    # https://github.com/symfony/symfony/blob/5.x/UPGRADE-5.2.md#security
    $rectorConfig->ruleWithConfiguration(RenamePropertyRector::class, [new RenameProperty('Symfony\\Component\\Security\\Http\\RememberMe\\AbstractRememberMeServices', 'providerKey', 'firewallName')]);
};
