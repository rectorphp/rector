<?php

declare(strict_types=1);

use Rector\Generic\Rector\ClassMethod\ArgumentAdderRector;
use Rector\Generic\ValueObject\ArgumentAdder;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Symfony3\Rector\ClassConstFetch\ConsoleExceptionToErrorEventConstantRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ArgumentAdderRector::class)
        ->call('configure', [[
            ArgumentAdderRector::ADDED_ARGUMENTS => ValueObjectInliner::inline([
                new ArgumentAdder(
                    'Symfony\Component\DependencyInjection\ContainerBuilder',
                    'compile',
                    2,
                    '__unknown__',
                    0
                ),
                new ArgumentAdder(
                    'Symfony\Component\DependencyInjection\ContainerBuilder',
                    'addCompilerPass',
                    2,
                    'priority',
                    0
                ),
                new ArgumentAdder(
                    'Symfony\Component\DependencyInjection\Compiler\ServiceReferenceGraph',
                    'connect',
                    6,
                    'weak',
                    false
                ),
            ]),
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
            RenameMethodRector::METHOD_CALL_RENAMES => ValueObjectInliner::inline([
                new MethodCallRename('Symfony\Component\DependencyInjection\Container', 'isFrozen', 'isCompiled'),
            ]),
        ]]);
};
