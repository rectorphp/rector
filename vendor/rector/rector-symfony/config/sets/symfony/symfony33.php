<?php

declare (strict_types=1);
namespace RectorPrefix20220607;

use Rector\Arguments\Rector\ClassMethod\ArgumentAdderRector;
use Rector\Arguments\ValueObject\ArgumentAdder;
use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Symfony\Rector\ClassConstFetch\ConsoleExceptionToErrorEventConstantRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(ArgumentAdderRector::class, [new ArgumentAdder('RectorPrefix20220607\\Symfony\\Component\\DependencyInjection\\ContainerBuilder', 'compile', 2, '__unknown__', 0), new ArgumentAdder('RectorPrefix20220607\\Symfony\\Component\\DependencyInjection\\ContainerBuilder', 'addCompilerPass', 2, 'priority', 0), new ArgumentAdder('RectorPrefix20220607\\Symfony\\Component\\DependencyInjection\\Compiler\\ServiceReferenceGraph', 'connect', 6, 'weak', \false)]);
    $rectorConfig->rule(ConsoleExceptionToErrorEventConstantRector::class);
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        # console
        'RectorPrefix20220607\\Symfony\\Component\\Console\\Event\\ConsoleExceptionEvent' => 'RectorPrefix20220607\\Symfony\\Component\\Console\\Event\\ConsoleErrorEvent',
        # debug
        'RectorPrefix20220607\\Symfony\\Component\\Debug\\Exception\\ContextErrorException' => 'ErrorException',
        # dependency-injection
        'RectorPrefix20220607\\Symfony\\Component\\DependencyInjection\\DefinitionDecorator' => 'RectorPrefix20220607\\Symfony\\Component\\DependencyInjection\\ChildDefinition',
        # framework bundle
        'RectorPrefix20220607\\Symfony\\Bundle\\FrameworkBundle\\DependencyInjection\\Compiler\\AddConsoleCommandPass' => 'RectorPrefix20220607\\Symfony\\Component\\Console\\DependencyInjection\\AddConsoleCommandPass',
        'RectorPrefix20220607\\Symfony\\Bundle\\FrameworkBundle\\DependencyInjection\\Compiler\\SerializerPass' => 'RectorPrefix20220607\\Symfony\\Component\\Serializer\\DependencyInjection\\SerializerPass',
        'RectorPrefix20220607\\Symfony\\Bundle\\FrameworkBundle\\DependencyInjection\\Compiler\\FormPass' => 'RectorPrefix20220607\\Symfony\\Component\\Form\\DependencyInjection\\FormPass',
        'RectorPrefix20220607\\Symfony\\Bundle\\FrameworkBundle\\EventListener\\SessionListener' => 'RectorPrefix20220607\\Symfony\\Component\\HttpKernel\\EventListener\\SessionListener',
        'RectorPrefix20220607\\Symfony\\Bundle\\FrameworkBundle\\EventListener\\TestSessionListenr' => 'RectorPrefix20220607\\Symfony\\Component\\HttpKernel\\EventListener\\TestSessionListener',
        'RectorPrefix20220607\\Symfony\\Bundle\\FrameworkBundle\\DependencyInjection\\Compiler\\ConfigCachePass' => 'RectorPrefix20220607\\Symfony\\Component\\Config\\DependencyInjection\\ConfigCachePass',
        'RectorPrefix20220607\\Symfony\\Bundle\\FrameworkBundle\\DependencyInjection\\Compiler\\PropertyInfoPass' => 'RectorPrefix20220607\\Symfony\\Component\\PropertyInfo\\DependencyInjection\\PropertyInfoPass',
    ]);
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [new MethodCallRename('RectorPrefix20220607\\Symfony\\Component\\DependencyInjection\\Container', 'isFrozen', 'isCompiled')]);
};
