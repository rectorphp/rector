<?php

declare(strict_types=1);

use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\Rector\ClassConstFetch\RenameClassConstantRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Symfony\Rector\ClassMethod\FormTypeGetParentRector;
use Rector\Symfony\Rector\ClassMethod\GetRequestRector;
use Rector\Symfony\Rector\ClassMethod\RemoveDefaultGetBlockPrefixRector;
use Rector\Symfony\Rector\MethodCall\CascadeValidationFormBuilderRector;
use Rector\Symfony\Rector\MethodCall\ChangeCollectionTypeOptionNameFromTypeToEntryTypeRector;
use Rector\Symfony\Rector\MethodCall\ChangeCollectionTypeOptionTypeFromStringToClassReferenceRector;
use Rector\Symfony\Rector\MethodCall\FormTypeInstanceToClassConstRector;
use Rector\Symfony\Rector\MethodCall\OptionNameRector;
use Rector\Symfony\Rector\MethodCall\ReadOnlyOptionToAttributeRector;
use Rector\Symfony\Rector\MethodCall\StringFormTypeToClassRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    # resources:
    # - https://github.com/symfony/symfony/blob/3.4/UPGRADE-3.0.md
    # php
    $services->set(GetRequestRector::class);

    $services->set(FormTypeGetParentRector::class);

    $services->set(OptionNameRector::class);

    $services->set(ReadOnlyOptionToAttributeRector::class);

    # forms
    $services->set(FormTypeInstanceToClassConstRector::class);

    $services->set(StringFormTypeToClassRector::class);

    $services->set(CascadeValidationFormBuilderRector::class);

    $services->set(RemoveDefaultGetBlockPrefixRector::class);

    # forms - collection
    $services->set(ChangeCollectionTypeOptionTypeFromStringToClassReferenceRector::class);

    $services->set(ChangeCollectionTypeOptionNameFromTypeToEntryTypeRector::class);

    $services->set(RenameClassConstantRector::class)
        ->call('configure', [[
            RenameClassConstantRector::OLD_TO_NEW_CONSTANTS_BY_CLASS => [
                'Symfony\Component\Form\FormEvents' => [
                    # general
                    # form
                    'PRE_BIND' => 'PRE_SUBMIT',
                    'BIND' => 'SUBMIT',
                    'POST_BIND' => 'POST_SUBMIT',
                ],
                'Symfony\Component\Form\Extension\Core\DataTransformer' => [
                    'ROUND_HALFEVEN' => 'ROUND_HALF_EVEN',
                    'ROUND_HALFUP' => 'ROUND_HALF_UP',
                    'ROUND_HALFDOWN' => 'ROUND_HALF_DOWN',
                ],
            ],
        ]]);

    $services->set(RenameMethodRector::class)
        ->call('configure', [[
            RenameMethodRector::OLD_TO_NEW_METHODS_BY_CLASS => [
                'Symfony\Component\ClassLoader\UniversalClassLoader\UniversalClassLoader' => [
                    # class loader
                    'registerNamespaces' => 'addPrefixes',
                    'registerPrefixes' => 'addPrefixes',
                    'registerNamespace' => 'addPrefix',
                    'registerPrefix' => 'addPrefix',
                    'getNamespaces' => 'getPrefixes',
                    'getNamespaceFallbacks' => 'getFallbackDirs',
                    'getPrefixFallbacks' => 'getFallbackDirs',
                ],
                'Symfony\Component\Form\AbstractType' => [
                    # form
                    'getName' => 'getBlockPrefix',
                    'setDefaultOptions' => 'configureOptions',
                ],
                'Symfony\Component\Form\FormTypeInterface' => [
                    'getName' => 'getBlockPrefix',
                    'setDefaultOptions' => 'configureOptions',
                ],
                'Symfony\Component\Form\ResolvedFormTypeInterface' => [
                    'getName' => 'getBlockPrefix',
                ],
                'Symfony\Component\Form\AbstractTypeExtension' => [
                    'setDefaultOptions' => 'configureOptions',
                ],
                'Symfony\Component\Form\Form' => [
                    'bind' => 'submit',
                    'isBound' => 'isSubmitted',
                ],
                'Symfony\Component\Process\Process' => [
                    # process
                    'setStdin' => 'setInput',
                    'getStdin' => 'getInput',
                ],
                'Symfony\Bridge\Monolog\Logger' => [
                    # monolog
                    'emerg' => 'emergency',
                    'crit' => 'critical',
                    'err' => 'error',
                    'warn' => 'warning',
                ],
                'Symfony\Component\HttpKernel\Log\LoggerInterface' => [
                    # http kernel
                    'emerg' => 'emergency',
                    'crit' => 'critical',
                    'err' => 'error',
                    'warn' => 'warning',
                ],
                'Symfony\Component\HttpKernel\Log\NullLogger' => [
                    'emerg' => 'emergency',
                    'crit' => 'critical',
                    'err' => 'error',
                    'warn' => 'warning',
                ],
                'Symfony\Component\PropertyAccess\PropertyAccess' => [
                    # property access
                    'getPropertyAccessor' => 'createPropertyAccessor',
                ],
                # serialazer
                'Symfony\Component\Serializer\Exception\Exception' => 'Symfony\Component\Serializer\Exception\ExceptionInterface',
                'Symfony\Component\Translation\Dumper\FileDumper' => [
                    # translator
                    'format' => 'formatCatalogue',
                ],
                'Symfony\Component\Translation\Translator' => [
                    'getMessages' => 'getCatalogue',
                ],
                'Symfony\Component\Validator\ConstraintViolationInterface' => [
                    # validator
                    'getMessageParameters' => 'getParameters',
                    'getMessagePluralization' => 'getPlural',
                ],
                'Symfony\Component\Validator\ConstraintViolation' => [
                    'getMessageParameters' => 'getParameters',
                    'getMessagePluralization' => 'getPlural',
                ],
            ],
        ]]);

    $services->set(RenameClassRector::class)
        ->call('configure', [[
            RenameClassRector::OLD_TO_NEW_CLASSES => [
                # class loader
                # partial with method rename
                'Symfony\Component\ClassLoader\UniversalClassLoader\UniversalClassLoader' => 'Symfony\Component\ClassLoader\ClassLoader',
                # console
                'Symfony\Component\Console\Helper\ProgressHelper' => 'Symfony\Component\Console\Helper\ProgressBar',
                # form
                'Symfony\Component\Form\Util\VirtualFormAwareIterator' => 'Symfony\Component\Form\Util\InheritDataAwareIterator',
                'Symfony\Component\Form\Tests\Extension\Core\Type\TypeTestCase' => 'Symfony\Component\Form\Test\TypeTestCase',
                'Symfony\Component\Form\Tests\FormIntegrationTestCase' => 'Symfony\Component\Form\Test\FormIntegrationTestCase',
                'Symfony\Component\Form\Tests\FormPerformanceTestCase' => 'Symfony\Component\Form\Test\FormPerformanceTestCase',
                'Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceListInterface' => 'Symfony\Component\Form\ChoiceList\ChoiceListInterface',
                'Symfony\Component\Form\Extension\Core\View\ChoiceView' => 'Symfony\Component\Form\ChoiceList\View\ChoiceView',
                'Symfony\Component\Form\Extension\Csrf\CsrfProvider\CsrfProviderInterface' => 'Symfony\Component\Security\Csrf\CsrfTokenManagerInterface',
                'Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceList' => 'Symfony\Component\Form\ChoiceList\ArrayChoiceList',
                'Symfony\Component\Form\Extension\Core\ChoiceList\LazyChoiceList' => 'Symfony\Component\Form\ChoiceList\LazyChoiceList',
                'Symfony\Component\Form\Extension\Core\ChoiceList\ObjectChoiceList' => 'Symfony\Component\Form\ChoiceList\ArrayChoiceList',
                'Symfony\Component\Form\Extension\Core\ChoiceList\SimpleChoiceList' => 'Symfony\Component\Form\ChoiceList\ArrayChoiceList',
                'Symfony\Component\Form\ChoiceList\ArrayKeyChoiceList' => 'Symfony\Component\Form\ChoiceList\ArrayChoiceList',
                # http kernel
                'Symfony\Component\HttpKernel\Debug\ErrorHandler' => 'Symfony\Component\Debug\ErrorHandler',
                'Symfony\Component\HttpKernel\Debug\ExceptionHandler' => 'Symfony\Component\Debug\ExceptionHandler',
                'Symfony\Component\HttpKernel\Exception\FatalErrorException' => 'Symfony\Component\Debug\Exception\FatalErrorException',
                'Symfony\Component\HttpKernel\Exception\FlattenException' => 'Symfony\Component\Debug\Exception\FlattenException',
                # partial with method rename
                'Symfony\Component\HttpKernel\Log\LoggerInterface' => 'Psr\Log\LoggerInterface',
                # event disptacher
                'Symfony\Component\HttpKernel\DependencyInjection\RegisterListenersPass' => 'Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass',
                # partial with methor rename
                'Symfony\Component\HttpKernel\Log\NullLogger' => 'Psr\Log\LoggerInterface',
                # monolog
                # partial with method rename
                'Symfony\Bridge\Monolog\Logger' => 'Psr\Log\LoggerInterface',
                # security
                'Symfony\Component\Security\Core\Authorization\Voter\AbstractVoter' => 'Symfony\Component\Security\Core\Authorization\Voter\Voter',
                # translator
                # partial with class rename
                'Symfony\Component\Translation\Translator' => 'Symfony\Component\Translation\TranslatorBagInterface',
                # twig
                'Symfony\Bundle\TwigBundle\TwigDefaultEscapingStrategy' => 'Twig_FileExtensionEscapingStrategy',
                # validator
                'Symfony\Component\Validator\Constraints\Collection\Optional' => 'Symfony\Component\Validator\Constraints\Optional',
                'Symfony\Component\Validator\Constraints\Collection\Required' => 'Symfony\Component\Validator\Constraints\Required',
                'Symfony\Component\Validator\MetadataInterface' => 'Symfony\Component\Validator\Mapping\MetadataInterface',
                'Symfony\Component\Validator\PropertyMetadataInterface' => 'Symfony\Component\Validator\Mapping\PropertyMetadataInterface',
                'Symfony\Component\Validator\PropertyMetadataContainerInterface' => 'Symfony\Component\Validator\Mapping\ClassMetadataInterface',
                'Symfony\Component\Validator\ClassBasedInterface' => 'Symfony\Component\Validator\Mapping\ClassMetadataInterface',
                'Symfony\Component\Validator\Mapping\ElementMetadata' => 'Symfony\Component\Validator\Mapping\GenericMetadata',
                'Symfony\Component\Validator\ExecutionContextInterface' => 'Symfony\Component\Validator\Context\ExecutionContextInterface',
                'Symfony\Component\Validator\Mapping\ClassMetadataFactory' => 'Symfony\Component\Validator\Mapping\Factory\LazyLoadingMetadataFactory',
                'Symfony\Component\Validator\Mapping\MetadataFactoryInterface' => 'Symfony\Component\Validator\Mapping\Factory\MetadataFactoryInterface',
                # swift mailer
                'Symfony\Bridge\Swiftmailer\DataCollector\MessageDataCollector' => 'Symfony\Bundle\SwiftmailerBundle\DataCollector\MessageDataCollector',
            ],
        ]]);
};
