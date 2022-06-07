<?php

declare (strict_types=1);
namespace RectorPrefix20220607;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\ClassConstFetch\RenameClassConstFetchRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Renaming\ValueObject\RenameClassConstFetch;
use Rector\Symfony\Rector\Class_\FormTypeWithDependencyToOptionsRector;
use Rector\Symfony\Rector\ClassMethod\FormTypeGetParentRector;
use Rector\Symfony\Rector\ClassMethod\GetRequestRector;
use Rector\Symfony\Rector\ClassMethod\RemoveDefaultGetBlockPrefixRector;
use Rector\Symfony\Rector\MethodCall\CascadeValidationFormBuilderRector;
use Rector\Symfony\Rector\MethodCall\ChangeCollectionTypeOptionNameFromTypeToEntryTypeRector;
use Rector\Symfony\Rector\MethodCall\ChangeStringCollectionOptionToConstantRector;
use Rector\Symfony\Rector\MethodCall\FormTypeInstanceToClassConstRector;
use Rector\Symfony\Rector\MethodCall\OptionNameRector;
use Rector\Symfony\Rector\MethodCall\ReadOnlyOptionToAttributeRector;
use Rector\Symfony\Rector\MethodCall\StringFormTypeToClassRector;
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
        FormTypeWithDependencyToOptionsRector::class,
        StringFormTypeToClassRector::class,
        CascadeValidationFormBuilderRector::class,
        RemoveDefaultGetBlockPrefixRector::class,
        // forms - collection
        ChangeStringCollectionOptionToConstantRector::class,
        ChangeCollectionTypeOptionNameFromTypeToEntryTypeRector::class,
    ]);
    $rectorConfig->ruleWithConfiguration(RenameClassConstFetchRector::class, [new RenameClassConstFetch('RectorPrefix20220607\\Symfony\\Component\\Form\\FormEvents', 'PRE_BIND', 'PRE_SUBMIT'), new RenameClassConstFetch('RectorPrefix20220607\\Symfony\\Component\\Form\\FormEvents', 'BIND', 'SUBMIT'), new RenameClassConstFetch('RectorPrefix20220607\\Symfony\\Component\\Form\\FormEvents', 'POST_BIND', 'POST_SUBMIT'), new RenameClassConstFetch('RectorPrefix20220607\\Symfony\\Component\\Form\\Extension\\Core\\DataTransformer', 'ROUND_HALFEVEN', 'ROUND_HALF_EVEN'), new RenameClassConstFetch('RectorPrefix20220607\\Symfony\\Component\\Form\\Extension\\Core\\DataTransformer', 'ROUND_HALFUP', 'ROUND_HALF_UP'), new RenameClassConstFetch('RectorPrefix20220607\\Symfony\\Component\\Form\\Extension\\Core\\DataTransformer', 'ROUND_HALFDOWN', 'ROUND_HALF_DOWN')]);
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [
        new MethodCallRename('RectorPrefix20220607\\Symfony\\Component\\ClassLoader\\UniversalClassLoader\\UniversalClassLoader', 'registerNamespaces', 'addPrefixes'),
        new MethodCallRename('RectorPrefix20220607\\Symfony\\Component\\ClassLoader\\UniversalClassLoader\\UniversalClassLoader', 'registerPrefixes', 'addPrefixes'),
        new MethodCallRename('RectorPrefix20220607\\Symfony\\Component\\ClassLoader\\UniversalClassLoader\\UniversalClassLoader', 'registerNamespace', 'addPrefix'),
        new MethodCallRename('RectorPrefix20220607\\Symfony\\Component\\ClassLoader\\UniversalClassLoader\\UniversalClassLoader', 'registerPrefix', 'addPrefix'),
        new MethodCallRename('RectorPrefix20220607\\Symfony\\Component\\ClassLoader\\UniversalClassLoader\\UniversalClassLoader', 'getNamespaces', 'getPrefixes'),
        new MethodCallRename('RectorPrefix20220607\\Symfony\\Component\\ClassLoader\\UniversalClassLoader\\UniversalClassLoader', 'getNamespaceFallbacks', 'getFallbackDirs'),
        new MethodCallRename('RectorPrefix20220607\\Symfony\\Component\\ClassLoader\\UniversalClassLoader\\UniversalClassLoader', 'getPrefixFallbacks', 'getFallbackDirs'),
        // form
        new MethodCallRename('RectorPrefix20220607\\Symfony\\Component\\Form\\AbstractType', 'getName', 'getBlockPrefix'),
        new MethodCallRename('RectorPrefix20220607\\Symfony\\Component\\Form\\AbstractType', 'setDefaultOptions', 'configureOptions'),
        new MethodCallRename('RectorPrefix20220607\\Symfony\\Component\\Form\\FormTypeInterface', 'getName', 'getBlockPrefix'),
        new MethodCallRename('RectorPrefix20220607\\Symfony\\Component\\Form\\FormTypeInterface', 'setDefaultOptions', 'configureOptions'),
        new MethodCallRename('RectorPrefix20220607\\Symfony\\Component\\Form\\ResolvedFormTypeInterface', 'getName', 'getBlockPrefix'),
        new MethodCallRename('RectorPrefix20220607\\Symfony\\Component\\Form\\AbstractTypeExtension', 'setDefaultOptions', 'configureOptions'),
        new MethodCallRename('RectorPrefix20220607\\Symfony\\Component\\Form\\Form', 'bind', 'submit'),
        new MethodCallRename('RectorPrefix20220607\\Symfony\\Component\\Form\\Form', 'isBound', 'isSubmitted'),
        // process
        new MethodCallRename('RectorPrefix20220607\\Symfony\\Component\\Process\\Process', 'setStdin', 'setInput'),
        new MethodCallRename('RectorPrefix20220607\\Symfony\\Component\\Process\\Process', 'getStdin', 'getInput'),
        // monolog
        new MethodCallRename('RectorPrefix20220607\\Symfony\\Bridge\\Monolog\\Logger', 'emerg', 'emergency'),
        new MethodCallRename('RectorPrefix20220607\\Symfony\\Bridge\\Monolog\\Logger', 'crit', 'critical'),
        new MethodCallRename('RectorPrefix20220607\\Symfony\\Bridge\\Monolog\\Logger', 'err', 'error'),
        new MethodCallRename('RectorPrefix20220607\\Symfony\\Bridge\\Monolog\\Logger', 'warn', 'warning'),
        # http kernel
        new MethodCallRename('RectorPrefix20220607\\Symfony\\Component\\HttpKernel\\Log\\LoggerInterface', 'emerg', 'emergency'),
        new MethodCallRename('RectorPrefix20220607\\Symfony\\Component\\HttpKernel\\Log\\LoggerInterface', 'crit', 'critical'),
        new MethodCallRename('RectorPrefix20220607\\Symfony\\Component\\HttpKernel\\Log\\LoggerInterface', 'err', 'error'),
        new MethodCallRename('RectorPrefix20220607\\Symfony\\Component\\HttpKernel\\Log\\LoggerInterface', 'warn', 'warning'),
        new MethodCallRename('RectorPrefix20220607\\Symfony\\Component\\HttpKernel\\Log\\NullLogger', 'emerg', 'emergency'),
        new MethodCallRename('RectorPrefix20220607\\Symfony\\Component\\HttpKernel\\Log\\NullLogger', 'crit', 'critical'),
        new MethodCallRename('RectorPrefix20220607\\Symfony\\Component\\HttpKernel\\Log\\NullLogger', 'err', 'error'),
        new MethodCallRename('RectorPrefix20220607\\Symfony\\Component\\HttpKernel\\Log\\NullLogger', 'warn', 'warning'),
        // property access
        new MethodCallRename('getPropertyAccessor', 'RectorPrefix20220607\\Symfony\\Component\\PropertyAccess\\PropertyAccess', 'createPropertyAccessor'),
        // translator
        new MethodCallRename('RectorPrefix20220607\\Symfony\\Component\\Translation\\Dumper\\FileDumper', 'format', 'formatCatalogue'),
        new MethodCallRename('RectorPrefix20220607\\Symfony\\Component\\Translation\\Translator', 'getMessages', 'getCatalogue'),
        // validator
        new MethodCallRename('RectorPrefix20220607\\Symfony\\Component\\Validator\\ConstraintViolationInterface', 'getMessageParameters', 'getParameters'),
        new MethodCallRename('RectorPrefix20220607\\Symfony\\Component\\Validator\\ConstraintViolationInterface', 'getMessagePluralization', 'getPlural'),
        new MethodCallRename('RectorPrefix20220607\\Symfony\\Component\\Validator\\ConstraintViolation', 'getMessageParameters', 'getParameters'),
        new MethodCallRename('RectorPrefix20220607\\Symfony\\Component\\Validator\\ConstraintViolation', 'getMessagePluralization', 'getPlural'),
    ]);
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        # class loader
        # partial with method rename
        'RectorPrefix20220607\\Symfony\\Component\\ClassLoader\\UniversalClassLoader\\UniversalClassLoader' => 'RectorPrefix20220607\\Symfony\\Component\\ClassLoader\\ClassLoader',
        # console
        'RectorPrefix20220607\\Symfony\\Component\\Console\\Helper\\ProgressHelper' => 'RectorPrefix20220607\\Symfony\\Component\\Console\\Helper\\ProgressBar',
        # form
        'RectorPrefix20220607\\Symfony\\Component\\Form\\Util\\VirtualFormAwareIterator' => 'RectorPrefix20220607\\Symfony\\Component\\Form\\Util\\InheritDataAwareIterator',
        'RectorPrefix20220607\\Symfony\\Component\\Form\\Tests\\Extension\\Core\\Type\\TypeTestCase' => 'RectorPrefix20220607\\Symfony\\Component\\Form\\Test\\TypeTestCase',
        'RectorPrefix20220607\\Symfony\\Component\\Form\\Tests\\FormIntegrationTestCase' => 'RectorPrefix20220607\\Symfony\\Component\\Form\\Test\\FormIntegrationTestCase',
        'RectorPrefix20220607\\Symfony\\Component\\Form\\Tests\\FormPerformanceTestCase' => 'RectorPrefix20220607\\Symfony\\Component\\Form\\Test\\FormPerformanceTestCase',
        'RectorPrefix20220607\\Symfony\\Component\\Form\\Extension\\Core\\ChoiceList\\ChoiceListInterface' => 'RectorPrefix20220607\\Symfony\\Component\\Form\\ChoiceList\\ChoiceListInterface',
        'RectorPrefix20220607\\Symfony\\Component\\Form\\Extension\\Core\\View\\ChoiceView' => 'RectorPrefix20220607\\Symfony\\Component\\Form\\ChoiceList\\View\\ChoiceView',
        'RectorPrefix20220607\\Symfony\\Component\\Form\\Extension\\Csrf\\CsrfProvider\\CsrfProviderInterface' => 'RectorPrefix20220607\\Symfony\\Component\\Security\\Csrf\\CsrfTokenManagerInterface',
        'RectorPrefix20220607\\Symfony\\Component\\Form\\Extension\\Core\\ChoiceList\\ChoiceList' => 'RectorPrefix20220607\\Symfony\\Component\\Form\\ChoiceList\\ArrayChoiceList',
        'RectorPrefix20220607\\Symfony\\Component\\Form\\Extension\\Core\\ChoiceList\\LazyChoiceList' => 'RectorPrefix20220607\\Symfony\\Component\\Form\\ChoiceList\\LazyChoiceList',
        'RectorPrefix20220607\\Symfony\\Component\\Form\\Extension\\Core\\ChoiceList\\ObjectChoiceList' => 'RectorPrefix20220607\\Symfony\\Component\\Form\\ChoiceList\\ArrayChoiceList',
        'RectorPrefix20220607\\Symfony\\Component\\Form\\Extension\\Core\\ChoiceList\\SimpleChoiceList' => 'RectorPrefix20220607\\Symfony\\Component\\Form\\ChoiceList\\ArrayChoiceList',
        'RectorPrefix20220607\\Symfony\\Component\\Form\\ChoiceList\\ArrayKeyChoiceList' => 'RectorPrefix20220607\\Symfony\\Component\\Form\\ChoiceList\\ArrayChoiceList',
        # http kernel
        'RectorPrefix20220607\\Symfony\\Component\\HttpKernel\\Debug\\ErrorHandler' => 'RectorPrefix20220607\\Symfony\\Component\\Debug\\ErrorHandler',
        'RectorPrefix20220607\\Symfony\\Component\\HttpKernel\\Debug\\ExceptionHandler' => 'RectorPrefix20220607\\Symfony\\Component\\Debug\\ExceptionHandler',
        'RectorPrefix20220607\\Symfony\\Component\\HttpKernel\\Exception\\FatalErrorException' => 'RectorPrefix20220607\\Symfony\\Component\\Debug\\Exception\\FatalErrorException',
        'RectorPrefix20220607\\Symfony\\Component\\HttpKernel\\Exception\\FlattenException' => 'RectorPrefix20220607\\Symfony\\Component\\Debug\\Exception\\FlattenException',
        # partial with method rename
        'RectorPrefix20220607\\Symfony\\Component\\HttpKernel\\Log\\LoggerInterface' => 'RectorPrefix20220607\\Psr\\Log\\LoggerInterface',
        # event disptacher
        'RectorPrefix20220607\\Symfony\\Component\\HttpKernel\\DependencyInjection\\RegisterListenersPass' => 'RectorPrefix20220607\\Symfony\\Component\\EventDispatcher\\DependencyInjection\\RegisterListenersPass',
        # partial with methor rename
        'RectorPrefix20220607\\Symfony\\Component\\HttpKernel\\Log\\NullLogger' => 'RectorPrefix20220607\\Psr\\Log\\LoggerInterface',
        # monolog
        # partial with method rename
        'RectorPrefix20220607\\Symfony\\Bridge\\Monolog\\Logger' => 'RectorPrefix20220607\\Psr\\Log\\LoggerInterface',
        # security
        'RectorPrefix20220607\\Symfony\\Component\\Security\\Core\\Authorization\\Voter\\AbstractVoter' => 'RectorPrefix20220607\\Symfony\\Component\\Security\\Core\\Authorization\\Voter\\Voter',
        # translator
        # partial with class rename
        'RectorPrefix20220607\\Symfony\\Component\\Translation\\Translator' => 'RectorPrefix20220607\\Symfony\\Component\\Translation\\TranslatorBagInterface',
        # twig
        'RectorPrefix20220607\\Symfony\\Bundle\\TwigBundle\\TwigDefaultEscapingStrategy' => 'Twig_FileExtensionEscapingStrategy',
        # validator
        'RectorPrefix20220607\\Symfony\\Component\\Validator\\Constraints\\Collection\\Optional' => 'RectorPrefix20220607\\Symfony\\Component\\Validator\\Constraints\\Optional',
        'RectorPrefix20220607\\Symfony\\Component\\Validator\\Constraints\\Collection\\Required' => 'RectorPrefix20220607\\Symfony\\Component\\Validator\\Constraints\\Required',
        'RectorPrefix20220607\\Symfony\\Component\\Validator\\MetadataInterface' => 'RectorPrefix20220607\\Symfony\\Component\\Validator\\Mapping\\MetadataInterface',
        'RectorPrefix20220607\\Symfony\\Component\\Validator\\PropertyMetadataInterface' => 'RectorPrefix20220607\\Symfony\\Component\\Validator\\Mapping\\PropertyMetadataInterface',
        'RectorPrefix20220607\\Symfony\\Component\\Validator\\PropertyMetadataContainerInterface' => 'RectorPrefix20220607\\Symfony\\Component\\Validator\\Mapping\\ClassMetadataInterface',
        'RectorPrefix20220607\\Symfony\\Component\\Validator\\ClassBasedInterface' => 'RectorPrefix20220607\\Symfony\\Component\\Validator\\Mapping\\ClassMetadataInterface',
        'RectorPrefix20220607\\Symfony\\Component\\Validator\\Mapping\\ElementMetadata' => 'RectorPrefix20220607\\Symfony\\Component\\Validator\\Mapping\\GenericMetadata',
        'RectorPrefix20220607\\Symfony\\Component\\Validator\\ExecutionContextInterface' => 'RectorPrefix20220607\\Symfony\\Component\\Validator\\Context\\ExecutionContextInterface',
        'RectorPrefix20220607\\Symfony\\Component\\Validator\\Mapping\\ClassMetadataFactory' => 'RectorPrefix20220607\\Symfony\\Component\\Validator\\Mapping\\Factory\\LazyLoadingMetadataFactory',
        'RectorPrefix20220607\\Symfony\\Component\\Validator\\Mapping\\MetadataFactoryInterface' => 'RectorPrefix20220607\\Symfony\\Component\\Validator\\Mapping\\Factory\\MetadataFactoryInterface',
        # swift mailer
        'RectorPrefix20220607\\Symfony\\Bridge\\Swiftmailer\\DataCollector\\MessageDataCollector' => 'RectorPrefix20220607\\Symfony\\Bundle\\SwiftmailerBundle\\DataCollector\\MessageDataCollector',
    ]);
};
