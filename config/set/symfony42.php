<?php

declare(strict_types=1);

use Rector\Generic\Rector\ClassMethod\ArgumentAdderRector;
use Rector\Generic\Rector\ClassMethod\ArgumentDefaultValueReplacerRector;
use Rector\Generic\Rector\ClassMethod\ArgumentRemoverRector;
use Rector\Generic\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Rector\Generic\Rector\ClassMethod\ChangeMethodVisibilityRector;
use Rector\Generic\Rector\ClassMethod\WrapReturnRector;
use Rector\Generic\Rector\New_\NewToStaticCallRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Symfony\Rector\MethodCall\ContainerGetToConstructorInjectionRector;
use Rector\Symfony\Rector\New_\RootNodeTreeBuilderRector;
use Rector\Symfony\Rector\New_\StringToArrayArgumentProcessRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

# https://github.com/symfony/symfony/pull/28447

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(NewToStaticCallRector::class)
        ->call('configure', [[
            NewToStaticCallRector::TYPE_TO_STATIC_CALLS => [
                'Symfony\Component\HttpFoundation\Cookie' => ['Symfony\Component\HttpFoundation\Cookie', 'create'],
            ],
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
            ArgumentAdderRector::POSITION_WITH_DEFAULT_VALUE_BY_METHOD_NAMES_BY_CLASS_TYPES => [
                'Symfony\Component\BrowserKit\Client' => [
                    'submit' => [
                        2 => [
                            # https://github.com/symfony/symfony/commit/fa2063efe43109aea093d6fbfc12d675dba82146
                            # https://github.com/symfony/symfony/commit/e3aa90f852f69040be19da3d8729cdf02d238ec7
                            'name' => 'serverParameters',
                            'default_value' => [],
                            'scope' => ['method_call'],
                        ],
                    ],
                ],
                'Symfony\Component\DomCrawler\Crawler' => [
                    'children' => [[
                        # https://github.com/symfony/symfony/commit/f634afdb6f573e4af8d89aaa605e0c7d4058676d
                        # $selector
                        'default_value' => null,
                        'scope' => ['method_call'],
                    ]],
                ],
                'Symfony\Component\Finder\Finder' => [
                    'sortByName' => [[
                        # $useNaturalSort
                        'default_value' => false,
                        'scope' => ['method_call'],
                    ]],
                ],
                'Symfony\Bridge\Monolog\Processor\DebugProcessor' => [
                    'getLogs' => [[
                        # $request
                        'default_value' => null,
                        'scope' => ['method_call'],
                    ]],
                    'countErrors' => [[
                        # $request
                        'default_value' => null,
                        'scope' => ['method_call'],
                    ]],
                ],
                'Symfony\Bridge\Monolog\Logger' => [
                    'getLogs' => [[
                        # $request
                        'default_value' => null,
                        'scope' => ['method_call'],
                    ]],
                    'countErrors' => [[
                        # $request
                        'default_value' => null,
                        'scope' => ['method_call'],
                    ]],
                ],
                'Symfony\Component\Serializer\Normalizer' => [
                    'handleCircularReference' => [
                        1 => [
                            # $format
                            'default_value' => null,
                            'scope' => ['method_call'],
                        ],
                        2 => [
                            # $context
                            'default_value' => [],
                            'scope' => ['method_call'],
                        ],
                    ],
                ],
            ],
        ]]);

    $services->set(RenameMethodRector::class)
        ->call('configure', [[
            RenameMethodRector::OLD_TO_NEW_METHODS_BY_CLASS => [
                'Symfony\Component\Cache\CacheItem' => [
                    'getPreviousTags' => 'getMetadata',
                ],
                'Symfony\Component\Form\AbstractTypeExtension' => [
                    'getExtendedType' => 'getExtendedTypes',
                ],
            ],
        ]]);

    $services->set(AddReturnTypeDeclarationRector::class)
        ->call('configure', [[
            AddReturnTypeDeclarationRector::TYPEHINT_FOR_METHOD_BY_CLASS => [
                'Symfony\Component\Form\AbstractTypeExtension' => [
                    'getExtendedTypes' => 'iterable',
                ],
            ],
        ]]);

    $services->set(ChangeMethodVisibilityRector::class)
        ->call('configure', [[
            ChangeMethodVisibilityRector::METHOD_TO_VISIBILITY_BY_CLASS => [
                'Symfony\Component\Form\AbstractTypeExtension' => [
                    'getExtendedTypes' => 'static',
                ],
            ],
        ]]);

    $services->set(WrapReturnRector::class)
        ->call('configure', [[
            WrapReturnRector::TYPE_TO_METHOD_TO_WRAP => [
                'Symfony\Component\Form\AbstractTypeExtension' => [
                    'getExtendedTypes' => 'array',
                ],
            ],
        ]]);

    $services->set(ArgumentDefaultValueReplacerRector::class)
        ->call('configure', [[
            ArgumentDefaultValueReplacerRector::REPLACES_BY_METHOD_AND_TYPES => [
                'Symfony\Component\HttpFoundation\Cookie' => [
                    '__construct' => [
                        5 => [
                            # https://github.com/symfony/symfony/commit/9493cfd5f2366dab19bbdde0d0291d0575454567
                            'before' => false,
                            'after' => null,
                        ],
                        8 => [
                            'before' => null,
                            'after' => 'lax',
                        ],
                    ],
                ],
            ],
        ]]);

    $services->set(ArgumentRemoverRector::class)
        ->call('configure', [[
            ArgumentRemoverRector::POSITIONS_BY_METHOD_NAME_BY_CLASS_TYPE => [
                'Symfony\Component\HttpKernel\DataCollector\ConfigDataCollector' => [
                    '__construct' => [
                        # https://github.com/symfony/symfony/commit/f5c355e1ba399a1b3512367647d902148bdaf09f
                        null,
                        null,
                    ],
                ],
            ],
        ]]);
};
