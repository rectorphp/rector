<?php

declare(strict_types=1);

use Rector\Generic\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Rector\Generic\Rector\ClassMethod\ArgumentAdderRector;

use Rector\Generic\Rector\ClassMethod\ArgumentDefaultValueReplacerRector;
use Rector\Generic\Rector\ClassMethod\ArgumentRemoverRector;
use Rector\Generic\Rector\ClassMethod\ChangeMethodVisibilityRector;
use Rector\Generic\Rector\ClassMethod\WrapReturnRector;
use Rector\Generic\ValueObject\AddedArgument;
use Rector\Generic\ValueObject\MethodReturnType;
use Rector\Generic\ValueObject\MethodVisibility;
use Rector\Generic\ValueObject\RemovedArgument;
use Rector\Generic\ValueObject\ReplacedArgument;
use Rector\Generic\ValueObject\TypeMethodWrap;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Symfony\Rector\MethodCall\ContainerGetToConstructorInjectionRector;
use Rector\Symfony\Rector\New_\RootNodeTreeBuilderRector;
use Rector\Symfony\Rector\New_\StringToArrayArgumentProcessRector;
use function Rector\SymfonyPhpConfig\inline_value_objects;
use Rector\Transform\Rector\New_\NewToStaticCallRector;
use Rector\Transform\ValueObject\NewToStaticCall;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

# https://github.com/symfony/symfony/pull/28447

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(NewToStaticCallRector::class)
        ->call('configure', [[
            NewToStaticCallRector::TYPE_TO_STATIC_CALLS => inline_value_objects([
                new NewToStaticCall(
                    'Symfony\Component\HttpFoundation\Cookie',
                    'Symfony\Component\HttpFoundation\Cookie',
                    'create'
                ),
            ]),
        ]]);

    $services->set(RenameClassRector::class)
        ->call('configure', [[
            RenameClassRector::OLD_TO_NEW_CLASSES => [
                # https://github.com/symfony/symfony/commit/a7e319d9e1316e2e18843f8ce15b67a8693e5bf9
                'Symfony\Bundle\FrameworkBundle\Controller\Controller' => 'Symfony\Bundle\FrameworkBundle\Controller\AbstractController',
                # https://github.com/symfony/symfony/commit/744bf0e7ac3ecf240d0bf055cc58f881bb0b3ec0
                'Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand' => 'Symfony\Component\Console\Command\Command',
                'Symfony\Component\Translation\TranslatorInterface' => 'Symfony\Contracts\Translation\TranslatorInterface',
            ],
        ]]);

    # related to "Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand" deprecation, see https://github.com/rectorphp/rector/issues/1629
    $services->set(ContainerGetToConstructorInjectionRector::class);

    # https://symfony.com/blog/new-in-symfony-4-2-important-deprecations
    $services->set(StringToArrayArgumentProcessRector::class);

    $services->set(RootNodeTreeBuilderRector::class);

    $services->set(ArgumentAdderRector::class)
        ->call('configure', [[
            ArgumentAdderRector::ADDED_ARGUMENTS => inline_value_objects([
                // https://github.com/symfony/symfony/commit/fa2063efe43109aea093d6fbfc12d675dba82146
                // https://github.com/symfony/symfony/commit/e3aa90f852f69040be19da3d8729cdf02d238ec7
                new AddedArgument(
                    'Symfony\Component\BrowserKit\Client',
                    'submit',
                    2,
                    'serverParameters',
                    [],
                    null,
                    ArgumentAdderRector::SCOPE_METHOD_CALL
                ),
                new AddedArgument(
                    'Symfony\Component\DomCrawler\Crawler',
                    'children',
                    0,
                    null,
                    null,
                    null,
                    ArgumentAdderRector::SCOPE_METHOD_CALL
                ),
                new AddedArgument(
                    'Symfony\Component\Finder\Finder',
                    'sortByName',
                    0,
                    null,
                    false,
                    null,
                    ArgumentAdderRector::SCOPE_METHOD_CALL
                ),
                new AddedArgument(
                    'Symfony\Bridge\Monolog\Processor\DebugProcessor',
                    'getLogs',
                    0,
                    null,
                    null,
                    null,
                    ArgumentAdderRector::SCOPE_METHOD_CALL
                ),
                new AddedArgument(
                    'Symfony\Bridge\Monolog\Processor\DebugProcessor',
                    'countErrors',
                    0,
                    'default_value',
                    null,
                    null,
                    ArgumentAdderRector::SCOPE_METHOD_CALL
                ),
                new AddedArgument(
                    'Symfony\Bridge\Monolog\Logger',
                    'getLogs',
                    0,
                    'default_value',
                    null,
                    null,
                    ArgumentAdderRector::SCOPE_METHOD_CALL
                ),
                new AddedArgument(
                    'Symfony\Bridge\Monolog\Logger',
                    'countErrors',
                    0,
                    'default_value',
                    null,
                    null,
                    ArgumentAdderRector::SCOPE_METHOD_CALL
                ),
                new AddedArgument(
                    'Symfony\Component\Serializer\Normalizer',
                    'handleCircularReference',
                    1,
                    null,
                    null,
                    null,
                    ArgumentAdderRector::SCOPE_METHOD_CALL
                ),
                new AddedArgument(
                    'Symfony\Component\Serializer\Normalizer',
                    'handleCircularReference',
                    2,
                    null,
                    null,
                    null,
                    ArgumentAdderRector::SCOPE_METHOD_CALL
                ),
            ]),
        ]]);

    $services->set(RenameMethodRector::class)
        ->call('configure', [[
            RenameMethodRector::METHOD_CALL_RENAMES => inline_value_objects([
                new MethodCallRename('Symfony\Component\Cache\CacheItem', 'getPreviousTags', 'getMetadata'),
                new MethodCallRename(
                    'Symfony\Component\Form\AbstractTypeExtension',
                    'getExtendedType',
                    'getExtendedTypes'
                ),
            ]),
        ]]);

    $services->set(AddReturnTypeDeclarationRector::class)
        ->call('configure', [[
            AddReturnTypeDeclarationRector::METHOD_RETURN_TYPES => inline_value_objects([
                new MethodReturnType('Symfony\Component\Form\AbstractTypeExtension', 'getExtendedTypes', 'iterable'),
            ]),
        ]]);

    $services->set(ChangeMethodVisibilityRector::class)
        ->call('configure', [[
            ChangeMethodVisibilityRector::METHOD_VISIBILITIES => inline_value_objects([
                new MethodVisibility('Symfony\Component\Form\AbstractTypeExtension', 'getExtendedTypes', 'static'),
            ]),
        ]]);

    $services->set(WrapReturnRector::class)
        ->call('configure', [[
            WrapReturnRector::TYPE_METHOD_WRAPS => inline_value_objects([
                new TypeMethodWrap('Symfony\Component\Form\AbstractTypeExtension', 'getExtendedTypes', true),
            ]),
        ]]);

    $services->set(ArgumentDefaultValueReplacerRector::class)
        ->call('configure', [[
            // https://github.com/symfony/symfony/commit/9493cfd5f2366dab19bbdde0d0291d0575454567
            ArgumentDefaultValueReplacerRector::REPLACED_ARGUMENTS => inline_value_objects([
                new ReplacedArgument('Symfony\Component\HttpFoundation\Cookie', '__construct', 5, false, null),
                new ReplacedArgument('Symfony\Component\HttpFoundation\Cookie', '__construct', 8, null, 'lax'),
            ]),
        ]]);

    $services->set(ArgumentRemoverRector::class)
        ->call('configure', [[
            # https://github.com/symfony/symfony/commit/f5c355e1ba399a1b3512367647d902148bdaf09f
            ArgumentRemoverRector::REMOVED_ARGUMENTS => inline_value_objects([
                new RemovedArgument(
                    'Symfony\Component\HttpKernel\DataCollector\ConfigDataCollector',
                    '__construct',
                    0,
                    null
                ),
                new RemovedArgument(
                    'Symfony\Component\HttpKernel\DataCollector\ConfigDataCollector',
                    '__construct',
                    1,
                    null
                ),
            ]),
        ]]);
};
