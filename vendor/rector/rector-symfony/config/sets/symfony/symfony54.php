<?php

declare (strict_types=1);
namespace RectorPrefix20220501;

use Rector\Config\RectorConfig;
use Rector\Php80\Rector\Class_\AnnotationToAttributeRector;
use Rector\Php80\ValueObject\AnnotationToAttribute;
use Rector\Renaming\Rector\ClassConstFetch\RenameClassConstFetchRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Renaming\ValueObject\RenameClassAndConstFetch;
use Rector\Renaming\ValueObject\RenameClassConstFetch;
use Rector\Symfony\Set\SymfonySetList;
# https://github.com/symfony/symfony/blob/5.x/UPGRADE-5.4.md
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->sets([\Rector\Symfony\Set\SymfonySetList::ANNOTATIONS_TO_ATTRIBUTES]);
    // @see https://symfony.com/blog/new-in-symfony-5-4-nested-validation-attributes
    // @see https://github.com/symfony/symfony/pull/41994
    $rectorConfig->ruleWithConfiguration(\Rector\Php80\Rector\Class_\AnnotationToAttributeRector::class, [new \Rector\Php80\ValueObject\AnnotationToAttribute('Symfony\\Component\\Validator\\Constraints\\All'), new \Rector\Php80\ValueObject\AnnotationToAttribute('Symfony\\Component\\Validator\\Constraints\\Collection'), new \Rector\Php80\ValueObject\AnnotationToAttribute('Symfony\\Component\\Validator\\Constraints\\AtLeastOneOf'), new \Rector\Php80\ValueObject\AnnotationToAttribute('Symfony\\Component\\Validator\\Constraints\\Sequentially')]);
    $rectorConfig->ruleWithConfiguration(\Rector\Renaming\Rector\MethodCall\RenameMethodRector::class, [
        // @see https://github.com/symfony/symfony/pull/42582
        new \Rector\Renaming\ValueObject\MethodCallRename('Symfony\\Bundle\\SecurityBundle\\Security\\FirewallConfig', 'getListeners', 'getAuthenticators'),
    ]);
    $rectorConfig->ruleWithConfiguration(\Rector\Renaming\Rector\ClassConstFetch\RenameClassConstFetchRector::class, [
        new \Rector\Renaming\ValueObject\RenameClassAndConstFetch('Symfony\\Component\\Security\\Core\\AuthenticationEvents', 'AUTHENTICATION_SUCCESS', 'Symfony\\Component\\Security\\Core\\Event\\AuthenticationSuccessEvent', 'class'),
        new \Rector\Renaming\ValueObject\RenameClassAndConstFetch('Symfony\\Component\\Security\\Core\\AuthenticationEvents', 'AUTHENTICATION_FAILURE', 'Symfony\\Component\\Security\\Core\\Event\\AuthenticationFailureEvent', 'class'),
        // @see https://github.com/symfony/symfony/pull/42510
        new \Rector\Renaming\ValueObject\RenameClassConstFetch('Symfony\\Component\\Security\\Core\\Authorization\\Voter\\AuthenticatedVoter', 'IS_ANONYMOUS', 'PUBLIC_ACCESS'),
        new \Rector\Renaming\ValueObject\RenameClassConstFetch('Symfony\\Component\\Security\\Core\\Authorization\\Voter\\AuthenticatedVoter', 'IS_AUTHENTICATED_ANONYMOUSLY', 'PUBLIC_ACCESS'),
    ]);
    $rectorConfig->ruleWithConfiguration(\Rector\Renaming\Rector\Name\RenameClassRector::class, [
        // @see https://github.com/symfony/symfony/pull/42050
        'Symfony\\Component\\Security\\Http\\Event\\DeauthenticatedEvent' => 'Symfony\\Component\\Security\\Http\\Event\\TokenDeauthenticatedEvent',
    ]);
};
