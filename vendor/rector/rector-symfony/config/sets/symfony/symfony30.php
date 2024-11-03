<?php

declare (strict_types=1);
namespace RectorPrefix202411;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\ClassConstFetch\RenameClassConstFetchRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Renaming\ValueObject\RenameClassConstFetch;
use Rector\Symfony\Symfony30\Rector\ClassMethod\FormTypeGetParentRector;
use Rector\Symfony\Symfony30\Rector\ClassMethod\GetRequestRector;
use Rector\Symfony\Symfony30\Rector\ClassMethod\RemoveDefaultGetBlockPrefixRector;
use Rector\Symfony\Symfony30\Rector\MethodCall\ChangeStringCollectionOptionToConstantRector;
use Rector\Symfony\Symfony30\Rector\MethodCall\FormTypeInstanceToClassConstRector;
use Rector\Symfony\Symfony30\Rector\MethodCall\OptionNameRector;
use Rector\Symfony\Symfony30\Rector\MethodCall\ReadOnlyOptionToAttributeRector;
use Rector\Symfony\Symfony30\Rector\MethodCall\StringFormTypeToClassRector;
return static function (RectorConfig $rectorConfig) : void {
    # resources:
    # - https://github.com/symfony/symfony/blob/3.4/UPGRADE-3.0.md
    $rectorConfig->rules([
        // php
        GetRequestRector::class,
        FormTypeGetParentRector::class,
        OptionNameRector::class,
        ReadOnlyOptionToAttributeRector::class,
        // forms
        FormTypeInstanceToClassConstRector::class,
        StringFormTypeToClassRector::class,
        RemoveDefaultGetBlockPrefixRector::class,
        // forms - collection
        ChangeStringCollectionOptionToConstantRector::class,
    ]);
    $rectorConfig->ruleWithConfiguration(RenameClassConstFetchRector::class, [new RenameClassConstFetch('Symfony\\Component\\Form\\FormEvents', 'PRE_BIND', 'PRE_SUBMIT'), new RenameClassConstFetch('Symfony\\Component\\Form\\FormEvents', 'BIND', 'SUBMIT'), new RenameClassConstFetch('Symfony\\Component\\Form\\FormEvents', 'POST_BIND', 'POST_SUBMIT'), new RenameClassConstFetch('Symfony\\Component\\Form\\Extension\\Core\\DataTransformer', 'ROUND_HALFEVEN', 'ROUND_HALF_EVEN'), new RenameClassConstFetch('Symfony\\Component\\Form\\Extension\\Core\\DataTransformer', 'ROUND_HALFUP', 'ROUND_HALF_UP'), new RenameClassConstFetch('Symfony\\Component\\Form\\Extension\\Core\\DataTransformer', 'ROUND_HALFDOWN', 'ROUND_HALF_DOWN')]);
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [
        new MethodCallRename('Symfony\\Component\\ClassLoader\\UniversalClassLoader\\UniversalClassLoader', 'registerNamespaces', 'addPrefixes'),
        new MethodCallRename('Symfony\\Component\\ClassLoader\\UniversalClassLoader\\UniversalClassLoader', 'registerPrefixes', 'addPrefixes'),
        new MethodCallRename('Symfony\\Component\\ClassLoader\\UniversalClassLoader\\UniversalClassLoader', 'registerNamespace', 'addPrefix'),
        new MethodCallRename('Symfony\\Component\\ClassLoader\\UniversalClassLoader\\UniversalClassLoader', 'registerPrefix', 'addPrefix'),
        new MethodCallRename('Symfony\\Component\\ClassLoader\\UniversalClassLoader\\UniversalClassLoader', 'getNamespaces', 'getPrefixes'),
        new MethodCallRename('Symfony\\Component\\ClassLoader\\UniversalClassLoader\\UniversalClassLoader', 'getNamespaceFallbacks', 'getFallbackDirs'),
        new MethodCallRename('Symfony\\Component\\ClassLoader\\UniversalClassLoader\\UniversalClassLoader', 'getPrefixFallbacks', 'getFallbackDirs'),
        // form
        new MethodCallRename('Symfony\\Component\\Form\\AbstractType', 'getName', 'getBlockPrefix'),
        new MethodCallRename('Symfony\\Component\\Form\\FormTypeInterface', 'getName', 'getBlockPrefix'),
        new MethodCallRename('Symfony\\Component\\Form\\FormTypeInterface', 'setDefaultOptions', 'configureOptions'),
        new MethodCallRename('Symfony\\Component\\Form\\ResolvedFormTypeInterface', 'getName', 'getBlockPrefix'),
        new MethodCallRename('Symfony\\Component\\Form\\AbstractTypeExtension', 'setDefaultOptions', 'configureOptions'),
        new MethodCallRename('Symfony\\Component\\Form\\Form', 'bind', 'submit'),
        new MethodCallRename('Symfony\\Component\\Form\\Form', 'isBound', 'isSubmitted'),
        // process
        new MethodCallRename('Symfony\\Component\\Process\\Process', 'setStdin', 'setInput'),
        new MethodCallRename('Symfony\\Component\\Process\\Process', 'getStdin', 'getInput'),
        // monolog
        new MethodCallRename('Symfony\\Bridge\\Monolog\\Logger', 'emerg', 'emergency'),
        new MethodCallRename('Symfony\\Bridge\\Monolog\\Logger', 'crit', 'critical'),
        new MethodCallRename('Symfony\\Bridge\\Monolog\\Logger', 'err', 'error'),
        new MethodCallRename('Symfony\\Bridge\\Monolog\\Logger', 'warn', 'warning'),
        # http kernel
        new MethodCallRename('Symfony\\Component\\HttpKernel\\Log\\LoggerInterface', 'emerg', 'emergency'),
        new MethodCallRename('Symfony\\Component\\HttpKernel\\Log\\LoggerInterface', 'crit', 'critical'),
        new MethodCallRename('Symfony\\Component\\HttpKernel\\Log\\LoggerInterface', 'err', 'error'),
        new MethodCallRename('Symfony\\Component\\HttpKernel\\Log\\LoggerInterface', 'warn', 'warning'),
        new MethodCallRename('Symfony\\Component\\HttpKernel\\Log\\NullLogger', 'emerg', 'emergency'),
        new MethodCallRename('Symfony\\Component\\HttpKernel\\Log\\NullLogger', 'crit', 'critical'),
        new MethodCallRename('Symfony\\Component\\HttpKernel\\Log\\NullLogger', 'err', 'error'),
        new MethodCallRename('Symfony\\Component\\HttpKernel\\Log\\NullLogger', 'warn', 'warning'),
        // property access
        new MethodCallRename('Symfony\\Component\\PropertyAccess\\PropertyAccess', 'getPropertyAccessor', 'createPropertyAccessor'),
        // translator
        new MethodCallRename('Symfony\\Component\\Translation\\Dumper\\FileDumper', 'format', 'formatCatalogue'),
        new MethodCallRename('Symfony\\Component\\Translation\\Translator', 'getMessages', 'getCatalogue'),
        // validator
        new MethodCallRename('Symfony\\Component\\Validator\\ConstraintViolationInterface', 'getMessageParameters', 'getParameters'),
        new MethodCallRename('Symfony\\Component\\Validator\\ConstraintViolationInterface', 'getMessagePluralization', 'getPlural'),
        new MethodCallRename('Symfony\\Component\\Validator\\ConstraintViolation', 'getMessageParameters', 'getParameters'),
        new MethodCallRename('Symfony\\Component\\Validator\\ConstraintViolation', 'getMessagePluralization', 'getPlural'),
    ]);
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        # class loader
        # partial with method rename
        'Symfony\\Component\\ClassLoader\\UniversalClassLoader\\UniversalClassLoader' => 'Symfony\\Component\\ClassLoader\\ClassLoader',
        # console
        'Symfony\\Component\\Console\\Helper\\ProgressHelper' => 'Symfony\\Component\\Console\\Helper\\ProgressBar',
        # form
        'Symfony\\Component\\Form\\Util\\VirtualFormAwareIterator' => 'Symfony\\Component\\Form\\Util\\InheritDataAwareIterator',
        'Symfony\\Component\\Form\\Tests\\Extension\\Core\\Type\\TypeTestCase' => 'Symfony\\Component\\Form\\Test\\TypeTestCase',
        'Symfony\\Component\\Form\\Tests\\FormIntegrationTestCase' => 'Symfony\\Component\\Form\\Test\\FormIntegrationTestCase',
        'Symfony\\Component\\Form\\Tests\\FormPerformanceTestCase' => 'Symfony\\Component\\Form\\Test\\FormPerformanceTestCase',
        'Symfony\\Component\\Form\\Extension\\Core\\ChoiceList\\ChoiceListInterface' => 'Symfony\\Component\\Form\\ChoiceList\\ChoiceListInterface',
        'Symfony\\Component\\Form\\Extension\\Core\\View\\ChoiceView' => 'Symfony\\Component\\Form\\ChoiceList\\View\\ChoiceView',
        'Symfony\\Component\\Form\\Extension\\Csrf\\CsrfProvider\\CsrfProviderInterface' => 'Symfony\\Component\\Security\\Csrf\\CsrfTokenManagerInterface',
        'Symfony\\Component\\Form\\Extension\\Core\\ChoiceList\\ChoiceList' => 'Symfony\\Component\\Form\\ChoiceList\\ArrayChoiceList',
        'Symfony\\Component\\Form\\Extension\\Core\\ChoiceList\\LazyChoiceList' => 'Symfony\\Component\\Form\\ChoiceList\\LazyChoiceList',
        'Symfony\\Component\\Form\\Extension\\Core\\ChoiceList\\ObjectChoiceList' => 'Symfony\\Component\\Form\\ChoiceList\\ArrayChoiceList',
        'Symfony\\Component\\Form\\Extension\\Core\\ChoiceList\\SimpleChoiceList' => 'Symfony\\Component\\Form\\ChoiceList\\ArrayChoiceList',
        'Symfony\\Component\\Form\\ChoiceList\\ArrayKeyChoiceList' => 'Symfony\\Component\\Form\\ChoiceList\\ArrayChoiceList',
        # http kernel
        'Symfony\\Component\\HttpKernel\\Debug\\ErrorHandler' => 'Symfony\\Component\\Debug\\ErrorHandler',
        'Symfony\\Component\\HttpKernel\\Debug\\ExceptionHandler' => 'Symfony\\Component\\Debug\\ExceptionHandler',
        'Symfony\\Component\\HttpKernel\\Exception\\FatalErrorException' => 'Symfony\\Component\\Debug\\Exception\\FatalErrorException',
        'Symfony\\Component\\HttpKernel\\Exception\\FlattenException' => 'Symfony\\Component\\Debug\\Exception\\FlattenException',
        # partial with method rename
        'Symfony\\Component\\HttpKernel\\Log\\LoggerInterface' => 'Psr\\Log\\LoggerInterface',
        # event disptacher
        'Symfony\\Component\\HttpKernel\\DependencyInjection\\RegisterListenersPass' => 'Symfony\\Component\\EventDispatcher\\DependencyInjection\\RegisterListenersPass',
        # partial with methor rename
        'Symfony\\Component\\HttpKernel\\Log\\NullLogger' => 'Psr\\Log\\LoggerInterface',
        # monolog
        # partial with method rename
        'Symfony\\Bridge\\Monolog\\Logger' => 'Psr\\Log\\LoggerInterface',
        # security
        'Symfony\\Component\\Security\\Core\\Authorization\\Voter\\AbstractVoter' => 'Symfony\\Component\\Security\\Core\\Authorization\\Voter\\Voter',
        # twig
        'Symfony\\Bundle\\TwigBundle\\TwigDefaultEscapingStrategy' => 'Twig_FileExtensionEscapingStrategy',
        # validator
        'Symfony\\Component\\Validator\\Constraints\\Collection\\Optional' => 'Symfony\\Component\\Validator\\Constraints\\Optional',
        'Symfony\\Component\\Validator\\Constraints\\Collection\\Required' => 'Symfony\\Component\\Validator\\Constraints\\Required',
        'Symfony\\Component\\Validator\\MetadataInterface' => 'Symfony\\Component\\Validator\\Mapping\\MetadataInterface',
        'Symfony\\Component\\Validator\\PropertyMetadataInterface' => 'Symfony\\Component\\Validator\\Mapping\\PropertyMetadataInterface',
        'Symfony\\Component\\Validator\\PropertyMetadataContainerInterface' => 'Symfony\\Component\\Validator\\Mapping\\ClassMetadataInterface',
        'Symfony\\Component\\Validator\\ClassBasedInterface' => 'Symfony\\Component\\Validator\\Mapping\\ClassMetadataInterface',
        'Symfony\\Component\\Validator\\Mapping\\ElementMetadata' => 'Symfony\\Component\\Validator\\Mapping\\GenericMetadata',
        'Symfony\\Component\\Validator\\ExecutionContextInterface' => 'Symfony\\Component\\Validator\\Context\\ExecutionContextInterface',
        'Symfony\\Component\\Validator\\Mapping\\ClassMetadataFactory' => 'Symfony\\Component\\Validator\\Mapping\\Factory\\LazyLoadingMetadataFactory',
        'Symfony\\Component\\Validator\\Mapping\\MetadataFactoryInterface' => 'Symfony\\Component\\Validator\\Mapping\\Factory\\MetadataFactoryInterface',
        # swift mailer
        'Symfony\\Bridge\\Swiftmailer\\DataCollector\\MessageDataCollector' => 'Symfony\\Bundle\\SwiftmailerBundle\\DataCollector\\MessageDataCollector',
    ]);
};
