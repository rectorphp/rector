<?php

declare (strict_types=1);
namespace RectorPrefix20211020;

use Rector\Renaming\Rector\ClassConstFetch\RenameClassConstFetchRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Renaming\ValueObject\RenameClassConstFetch;
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
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    # resources:
    # - https://github.com/symfony/symfony/blob/3.4/UPGRADE-3.0.md
    # php
    $services->set(\Rector\Symfony\Rector\ClassMethod\GetRequestRector::class);
    $services->set(\Rector\Symfony\Rector\ClassMethod\FormTypeGetParentRector::class);
    $services->set(\Rector\Symfony\Rector\MethodCall\OptionNameRector::class);
    $services->set(\Rector\Symfony\Rector\MethodCall\ReadOnlyOptionToAttributeRector::class);
    # forms
    $services->set(\Rector\Symfony\Rector\MethodCall\FormTypeInstanceToClassConstRector::class);
    $services->set(\Rector\Symfony\Rector\MethodCall\StringFormTypeToClassRector::class);
    $services->set(\Rector\Symfony\Rector\MethodCall\CascadeValidationFormBuilderRector::class);
    $services->set(\Rector\Symfony\Rector\ClassMethod\RemoveDefaultGetBlockPrefixRector::class);
    # forms - collection
    $services->set(\Rector\Symfony\Rector\MethodCall\ChangeStringCollectionOptionToConstantRector::class);
    $services->set(\Rector\Symfony\Rector\MethodCall\ChangeCollectionTypeOptionNameFromTypeToEntryTypeRector::class);
    $services->set(\Rector\Renaming\Rector\ClassConstFetch\RenameClassConstFetchRector::class)->call('configure', [[\Rector\Renaming\Rector\ClassConstFetch\RenameClassConstFetchRector::CLASS_CONSTANT_RENAME => \Symplify\SymfonyPhpConfig\ValueObjectInliner::inline([new \Rector\Renaming\ValueObject\RenameClassConstFetch('Symfony\\Component\\Form\\FormEvents', 'PRE_BIND', 'PRE_SUBMIT'), new \Rector\Renaming\ValueObject\RenameClassConstFetch('Symfony\\Component\\Form\\FormEvents', 'BIND', 'SUBMIT'), new \Rector\Renaming\ValueObject\RenameClassConstFetch('Symfony\\Component\\Form\\FormEvents', 'POST_BIND', 'POST_SUBMIT'), new \Rector\Renaming\ValueObject\RenameClassConstFetch('Symfony\\Component\\Form\\Extension\\Core\\DataTransformer', 'ROUND_HALFEVEN', 'ROUND_HALF_EVEN'), new \Rector\Renaming\ValueObject\RenameClassConstFetch('Symfony\\Component\\Form\\Extension\\Core\\DataTransformer', 'ROUND_HALFUP', 'ROUND_HALF_UP'), new \Rector\Renaming\ValueObject\RenameClassConstFetch('Symfony\\Component\\Form\\Extension\\Core\\DataTransformer', 'ROUND_HALFDOWN', 'ROUND_HALF_DOWN')])]]);
    $services->set(\Rector\Renaming\Rector\MethodCall\RenameMethodRector::class)->call('configure', [[\Rector\Renaming\Rector\MethodCall\RenameMethodRector::METHOD_CALL_RENAMES => \Symplify\SymfonyPhpConfig\ValueObjectInliner::inline([
        new \Rector\Renaming\ValueObject\MethodCallRename('Symfony\\Component\\ClassLoader\\UniversalClassLoader\\UniversalClassLoader', 'registerNamespaces', 'addPrefixes'),
        new \Rector\Renaming\ValueObject\MethodCallRename('Symfony\\Component\\ClassLoader\\UniversalClassLoader\\UniversalClassLoader', 'registerPrefixes', 'addPrefixes'),
        new \Rector\Renaming\ValueObject\MethodCallRename('Symfony\\Component\\ClassLoader\\UniversalClassLoader\\UniversalClassLoader', 'registerNamespace', 'addPrefix'),
        new \Rector\Renaming\ValueObject\MethodCallRename('Symfony\\Component\\ClassLoader\\UniversalClassLoader\\UniversalClassLoader', 'registerPrefix', 'addPrefix'),
        new \Rector\Renaming\ValueObject\MethodCallRename('Symfony\\Component\\ClassLoader\\UniversalClassLoader\\UniversalClassLoader', 'getNamespaces', 'getPrefixes'),
        new \Rector\Renaming\ValueObject\MethodCallRename('Symfony\\Component\\ClassLoader\\UniversalClassLoader\\UniversalClassLoader', 'getNamespaceFallbacks', 'getFallbackDirs'),
        new \Rector\Renaming\ValueObject\MethodCallRename('Symfony\\Component\\ClassLoader\\UniversalClassLoader\\UniversalClassLoader', 'getPrefixFallbacks', 'getFallbackDirs'),
        // form
        new \Rector\Renaming\ValueObject\MethodCallRename('Symfony\\Component\\Form\\AbstractType', 'getName', 'getBlockPrefix'),
        new \Rector\Renaming\ValueObject\MethodCallRename('Symfony\\Component\\Form\\AbstractType', 'setDefaultOptions', 'configureOptions'),
        new \Rector\Renaming\ValueObject\MethodCallRename('Symfony\\Component\\Form\\FormTypeInterface', 'getName', 'getBlockPrefix'),
        new \Rector\Renaming\ValueObject\MethodCallRename('Symfony\\Component\\Form\\FormTypeInterface', 'setDefaultOptions', 'configureOptions'),
        new \Rector\Renaming\ValueObject\MethodCallRename('Symfony\\Component\\Form\\ResolvedFormTypeInterface', 'getName', 'getBlockPrefix'),
        new \Rector\Renaming\ValueObject\MethodCallRename('Symfony\\Component\\Form\\AbstractTypeExtension', 'setDefaultOptions', 'configureOptions'),
        new \Rector\Renaming\ValueObject\MethodCallRename('Symfony\\Component\\Form\\Form', 'bind', 'submit'),
        new \Rector\Renaming\ValueObject\MethodCallRename('Symfony\\Component\\Form\\Form', 'isBound', 'isSubmitted'),
        // process
        new \Rector\Renaming\ValueObject\MethodCallRename('Symfony\\Component\\Process\\Process', 'setStdin', 'setInput'),
        new \Rector\Renaming\ValueObject\MethodCallRename('Symfony\\Component\\Process\\Process', 'getStdin', 'getInput'),
        // monolog
        new \Rector\Renaming\ValueObject\MethodCallRename('Symfony\\Bridge\\Monolog\\Logger', 'emerg', 'emergency'),
        new \Rector\Renaming\ValueObject\MethodCallRename('Symfony\\Bridge\\Monolog\\Logger', 'crit', 'critical'),
        new \Rector\Renaming\ValueObject\MethodCallRename('Symfony\\Bridge\\Monolog\\Logger', 'err', 'error'),
        new \Rector\Renaming\ValueObject\MethodCallRename('Symfony\\Bridge\\Monolog\\Logger', 'warn', 'warning'),
        # http kernel
        new \Rector\Renaming\ValueObject\MethodCallRename('Symfony\\Component\\HttpKernel\\Log\\LoggerInterface', 'emerg', 'emergency'),
        new \Rector\Renaming\ValueObject\MethodCallRename('Symfony\\Component\\HttpKernel\\Log\\LoggerInterface', 'crit', 'critical'),
        new \Rector\Renaming\ValueObject\MethodCallRename('Symfony\\Component\\HttpKernel\\Log\\LoggerInterface', 'err', 'error'),
        new \Rector\Renaming\ValueObject\MethodCallRename('Symfony\\Component\\HttpKernel\\Log\\LoggerInterface', 'warn', 'warning'),
        new \Rector\Renaming\ValueObject\MethodCallRename('Symfony\\Component\\HttpKernel\\Log\\NullLogger', 'emerg', 'emergency'),
        new \Rector\Renaming\ValueObject\MethodCallRename('Symfony\\Component\\HttpKernel\\Log\\NullLogger', 'crit', 'critical'),
        new \Rector\Renaming\ValueObject\MethodCallRename('Symfony\\Component\\HttpKernel\\Log\\NullLogger', 'err', 'error'),
        new \Rector\Renaming\ValueObject\MethodCallRename('Symfony\\Component\\HttpKernel\\Log\\NullLogger', 'warn', 'warning'),
        // property access
        new \Rector\Renaming\ValueObject\MethodCallRename('getPropertyAccessor', 'Symfony\\Component\\PropertyAccess\\PropertyAccess', 'createPropertyAccessor'),
        // translator
        new \Rector\Renaming\ValueObject\MethodCallRename('Symfony\\Component\\Translation\\Dumper\\FileDumper', 'format', 'formatCatalogue'),
        new \Rector\Renaming\ValueObject\MethodCallRename('Symfony\\Component\\Translation\\Translator', 'getMessages', 'getCatalogue'),
        // validator
        new \Rector\Renaming\ValueObject\MethodCallRename('Symfony\\Component\\Validator\\ConstraintViolationInterface', 'getMessageParameters', 'getParameters'),
        new \Rector\Renaming\ValueObject\MethodCallRename('Symfony\\Component\\Validator\\ConstraintViolationInterface', 'getMessagePluralization', 'getPlural'),
        new \Rector\Renaming\ValueObject\MethodCallRename('Symfony\\Component\\Validator\\ConstraintViolation', 'getMessageParameters', 'getParameters'),
        new \Rector\Renaming\ValueObject\MethodCallRename('Symfony\\Component\\Validator\\ConstraintViolation', 'getMessagePluralization', 'getPlural'),
    ])]]);
    $services->set(\Rector\Renaming\Rector\Name\RenameClassRector::class)->call('configure', [[\Rector\Renaming\Rector\Name\RenameClassRector::OLD_TO_NEW_CLASSES => [
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
        # translator
        # partial with class rename
        'Symfony\\Component\\Translation\\Translator' => 'Symfony\\Component\\Translation\\TranslatorBagInterface',
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
    ]]]);
};
