<?php

declare (strict_types=1);
namespace RectorPrefix202409;

use Rector\Config\RectorConfig;
use Rector\Php80\Rector\Class_\AnnotationToAttributeRector;
use Rector\Php80\ValueObject\AnnotationToAttribute;
use Rector\Renaming\Rector\ClassConstFetch\RenameClassConstFetchRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Renaming\ValueObject\RenameClassAndConstFetch;
use Rector\Symfony\Symfony62\Rector\Class_\MessageHandlerInterfaceToAttributeRector;
use Rector\Symfony\Symfony62\Rector\Class_\MessageSubscriberInterfaceToAttributeRector;
use Rector\Symfony\Symfony62\Rector\Class_\SecurityAttributeToIsGrantedAttributeRector;
use Rector\Symfony\Symfony62\Rector\ClassMethod\ClassMethod\ArgumentValueResolverToValueResolverRector;
use Rector\Symfony\Symfony62\Rector\ClassMethod\ParamConverterAttributeToMapEntityAttributeRector;
use Rector\Symfony\Symfony62\Rector\MethodCall\SimplifyFormRenderingRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([
        SimplifyFormRenderingRector::class,
        SecurityAttributeToIsGrantedAttributeRector::class,
        ParamConverterAttributeToMapEntityAttributeRector::class,
        // @see https://github.com/symfony/symfony/pull/47068, #[AsMessageHandler] attribute
        MessageHandlerInterfaceToAttributeRector::class,
        MessageSubscriberInterfaceToAttributeRector::class,
        // @see https://github.com/symfony/symfony/pull/47363
        ArgumentValueResolverToValueResolverRector::class,
    ]);
    // change to attribute before rename
    // https://symfony.com/blog/new-in-symfony-6-2-built-in-cache-security-template-and-doctrine-attributes
    // @see https://github.com/rectorphp/rector-symfony/issues/535#issuecomment-1783983383
    $rectorConfig->ruleWithConfiguration(AnnotationToAttributeRector::class, [
        // @see https://github.com/symfony/symfony/pull/46907
        new AnnotationToAttribute('Sensio\\Bundle\\FrameworkExtraBundle\\Configuration\\IsGranted'),
        // @see https://github.com/symfony/symfony/pull/46880
        new AnnotationToAttribute('Sensio\\Bundle\\FrameworkExtraBundle\\Configuration\\Cache'),
        // @see https://github.com/symfony/symfony/pull/46906
        new AnnotationToAttribute('Sensio\\Bundle\\FrameworkExtraBundle\\Configuration\\Template'),
    ]);
    // https://symfony.com/blog/new-in-symfony-6-2-built-in-cache-security-template-and-doctrine-attributes
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        // @see https://github.com/symfony/symfony/pull/46907
        'Sensio\\Bundle\\FrameworkExtraBundle\\Configuration\\IsGranted' => 'Symfony\\Component\\Security\\Http\\Attribute\\IsGranted',
        // @see https://github.com/symfony/symfony/pull/46880
        'Sensio\\Bundle\\FrameworkExtraBundle\\Configuration\\Cache' => 'Symfony\\Component\\HttpKernel\\Attribute\\Cache',
        // @see https://github.com/symfony/symfony/pull/46906
        'Sensio\\Bundle\\FrameworkExtraBundle\\Configuration\\Template' => 'Symfony\\Bridge\\Twig\\Attribute\\Template',
        // @see https://github.com/symfony/symfony/pull/46714
        'Symfony\\Component\\Mailer\\Bridge\\OhMySmtp\\Transport\\OhMySmtpApiTransport' => 'Symfony\\Component\\Mailer\\Bridge\\MailPace\\Transport\\MailPaceApiTransport',
        'Symfony\\Component\\Mailer\\Bridge\\OhMySmtp\\Transport\\OhMySmtpSmtpTransport' => 'Symfony\\Component\\Mailer\\Bridge\\MailPace\\Transport\\MailPaceSmtpTransport',
        'Symfony\\Component\\Mailer\\Bridge\\OhMySmtp\\Transport\\OhMySmtpTransportFactory' => 'Symfony\\Component\\Mailer\\Bridge\\MailPace\\Transport\\MailPaceTransportFactory',
        // @see https://github.com/symfony/symfony/pull/47363
        'Symfony\\Component\\HttpKernel\\Controller\\ArgumentValueResolverInterface' => 'Symfony\\Component\\HttpKernel\\Controller\\ValueResolverInterface',
        // @see https://github.com/symfony/symfony/pull/46094
        'Symfony\\Component\\Security\\Core\\Security' => 'Symfony\\Bundle\\SecurityBundle\\Security',
        // @see https://github.com/symfony/symfony/pull/46161
        'Symfony\\Component\\Translation\\Extractor\\PhpAstExtractor' => 'Symfony\\Component\\Translation\\Extractor\\PhpAstExtractor',
        // @see https://github.com/symfony/symfony/pull/47595
        'Symfony\\Component\\HttpFoundation\\ExpressionRequestMatcher' => 'Symfony\\Component\\HttpFoundation\\RequestMatcher\\ExpressionRequestMatcher',
        'Symfony\\Component\\HttpFoundation\\RequestMatcher' => 'Symfony\\Component\\HttpFoundation\\ChainRequestMatcher',
    ]);
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [
        // @see https://github.com/symfony/symfony/pull/46854
        new MethodCallRename('Symfony\\Bundle\\FrameworkBundle\\Controller\\AbstractController', 'renderForm', 'render'),
        // @see https://github.com/symfony/symfony/pull/45034
        new MethodCallRename('Symfony\\Component\\HttpFoundation\\Request', 'getContentType', 'getContentTypeFormat'),
        // @see https://github.com/symfony/symfony/pull/47711
        new MethodCallRename('Symfony\\Component\\Mime\\Email', 'attachPart', 'addPart'),
    ]);
    // @see https://github.com/symfony/symfony/pull/46094
    // @see https://github.com/symfony/symfony/pull/48554
    $rectorConfig->ruleWithConfiguration(RenameClassConstFetchRector::class, [new RenameClassAndConstFetch('Symfony\\Component\\Security\\Core\\Security', 'ACCESS_DENIED_ERROR', 'Symfony\\Component\\Security\\Http\\SecurityRequestAttributes', 'ACCESS_DENIED_ERROR'), new RenameClassAndConstFetch('Symfony\\Component\\Security\\Core\\Security', 'AUTHENTICATION_ERROR', 'Symfony\\Component\\Security\\Http\\SecurityRequestAttributes', 'AUTHENTICATION_ERROR'), new RenameClassAndConstFetch('Symfony\\Component\\Security\\Core\\Security', 'LAST_USERNAME', 'Symfony\\Component\\Security\\Http\\SecurityRequestAttributes', 'LAST_USERNAME'), new RenameClassAndConstFetch('Symfony\\Component\\Security\\Core\\Security', 'MAX_USERNAME_LENGTH', 'Symfony\\Component\\Security\\Http\\Authenticator\\Passport\\Badge\\UserBadge', 'MAX_USERNAME_LENGTH')]);
};
