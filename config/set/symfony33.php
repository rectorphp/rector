<?php

declare(strict_types=1);

use Rector\Generic\Rector\Argument\ArgumentAdderRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Symfony\Rector\ClassConstFetch\ConsoleExceptionToErrorEventConstantRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ArgumentAdderRector::class)
        ->call('configure', [[
            ArgumentAdderRector::POSITION_WITH_DEFAULT_VALUE_BY_METHOD_NAMES_BY_CLASS_TYPES => [
                'Symfony\Component\DependencyInjection\ContainerBuilder' => [
                    'compile' => [
                        2 => [
                            'name' => '__unknown__',
                            'default_value' => 0,
                        ],
                    ],
                    'addCompilerPass' => [
                        2 => [
                            'name' => 'priority',
                            'default_value' => 0,
                        ],
                    ],
                ],
                'Symfony\Component\DependencyInjection\Compiler\ServiceReferenceGraph' => [
                    'connect' => [
                        6 => [
                            'name' => 'weak',
                            'default_value' => false,
                        ],
                    ],
                ],
            ],
        ]]);

    $services->set(ConsoleExceptionToErrorEventConstantRector::class);

    $services->set(RenameClassRector::class)
        ->call('configure', [[
            RenameClassRector::OLD_TO_NEW_CLASSES => [
                # console
                'Symfony\Component\Console\Event\ConsoleExceptionEvent' => 'Symfony\Component\Console\Event\ConsoleErrorEvent',
                # debug
                'Symfony\Component\Debug\Exception\ContextErrorException' => 'ErrorException',
                # dependency-injection
                'Symfony\Component\DependencyInjection\DefinitionDecorator' => 'Symfony\Component\DependencyInjection\ChildDefinition',
                # framework bundle
                'Symfony\Bundle\FrameworkBundle\DependencyInjection\Compiler\AddConsoleCommandPass' => 'Symfony\Component\Console\DependencyInjection\AddConsoleCommandPass',
                'Symfony\Bundle\FrameworkBundle\DependencyInjection\Compiler\SerializerPass' => 'Symfony\Component\Serializer\DependencyInjection\SerializerPass',
                'Symfony\Bundle\FrameworkBundle\DependencyInjection\Compiler\FormPass' => 'Symfony\Component\Form\DependencyInjection\FormPass',
                'Symfony\Bundle\FrameworkBundle\EventListener\SessionListener' => 'Symfony\Component\HttpKernel\EventListener\SessionListener',
                'Symfony\Bundle\FrameworkBundle\EventListener\TestSessionListenr' => 'Symfony\Component\HttpKernel\EventListener\TestSessionListener',
                'Symfony\Bundle\FrameworkBundle\DependencyInjection\Compiler\ConfigCachePass' => 'Symfony\Component\Config\DependencyInjection\ConfigCachePass',
                'Symfony\Bundle\FrameworkBundle\DependencyInjection\Compiler\PropertyInfoPass' => 'Symfony\Component\PropertyInfo\DependencyInjection\PropertyInfoPass',
            ],
        ]]);

    $services->set(RenameMethodRector::class)
        ->call('configure', [[
            RenameMethodRector::OLD_TO_NEW_METHODS_BY_CLASS => [
                'Symfony\Component\DependencyInjection\Container' => [
                    # dependency-injection
                    'isFrozen' => 'isCompiled',
                ],
            ],
        ]]);
};
