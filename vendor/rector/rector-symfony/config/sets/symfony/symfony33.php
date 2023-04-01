<?php

declare (strict_types=1);
namespace RectorPrefix202304;

use Rector\Arguments\Rector\ClassMethod\ArgumentAdderRector;
use Rector\Arguments\ValueObject\ArgumentAdder;
use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Symfony\Rector\ClassConstFetch\ConsoleExceptionToErrorEventConstantRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(ArgumentAdderRector::class, [new ArgumentAdder('Symfony\\Component\\DependencyInjection\\ContainerBuilder', 'compile', 0, 'resolveEnvPlaceholders', \false), new ArgumentAdder('Symfony\\Component\\DependencyInjection\\ContainerBuilder', 'addCompilerPass', 2, 'priority', 0), new ArgumentAdder('Symfony\\Component\\DependencyInjection\\Compiler\\ServiceReferenceGraph', 'connect', 6, 'weak', \false)]);
    $rectorConfig->rule(ConsoleExceptionToErrorEventConstantRector::class);
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        # console
        'Symfony\\Component\\Console\\Event\\ConsoleExceptionEvent' => 'Symfony\\Component\\Console\\Event\\ConsoleErrorEvent',
        # debug
        'Symfony\\Component\\Debug\\Exception\\ContextErrorException' => 'ErrorException',
        # dependency-injection
        'Symfony\\Component\\DependencyInjection\\DefinitionDecorator' => 'Symfony\\Component\\DependencyInjection\\ChildDefinition',
        # framework bundle
        'Symfony\\Bundle\\FrameworkBundle\\DependencyInjection\\Compiler\\AddConsoleCommandPass' => 'Symfony\\Component\\Console\\DependencyInjection\\AddConsoleCommandPass',
        'Symfony\\Bundle\\FrameworkBundle\\DependencyInjection\\Compiler\\SerializerPass' => 'Symfony\\Component\\Serializer\\DependencyInjection\\SerializerPass',
        'Symfony\\Bundle\\FrameworkBundle\\DependencyInjection\\Compiler\\FormPass' => 'Symfony\\Component\\Form\\DependencyInjection\\FormPass',
        'Symfony\\Bundle\\FrameworkBundle\\EventListener\\SessionListener' => 'Symfony\\Component\\HttpKernel\\EventListener\\SessionListener',
        'Symfony\\Bundle\\FrameworkBundle\\EventListener\\TestSessionListenr' => 'Symfony\\Component\\HttpKernel\\EventListener\\TestSessionListener',
        'Symfony\\Bundle\\FrameworkBundle\\DependencyInjection\\Compiler\\ConfigCachePass' => 'Symfony\\Component\\Config\\DependencyInjection\\ConfigCachePass',
        'Symfony\\Bundle\\FrameworkBundle\\DependencyInjection\\Compiler\\PropertyInfoPass' => 'Symfony\\Component\\PropertyInfo\\DependencyInjection\\PropertyInfoPass',
    ]);
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [new MethodCallRename('Symfony\\Component\\DependencyInjection\\Container', 'isFrozen', 'isCompiled')]);
};
