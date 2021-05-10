<?php

declare (strict_types=1);
namespace RectorPrefix20210510\Symplify\SymplifyKernel\DependencyInjection\CompilerPass;

use RectorPrefix20210510\Symfony\Component\Console\Application;
use RectorPrefix20210510\Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use RectorPrefix20210510\Symfony\Component\DependencyInjection\ContainerBuilder;
use RectorPrefix20210510\Symfony\Component\DependencyInjection\Reference;
use RectorPrefix20210510\Symplify\SymplifyKernel\Console\AutowiredConsoleApplication;
use RectorPrefix20210510\Symplify\SymplifyKernel\Console\ConsoleApplicationFactory;
final class PrepareConsoleApplicationCompilerPass implements \RectorPrefix20210510\Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface
{
    public function process(\RectorPrefix20210510\Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder) : void
    {
        $consoleApplicationClass = $this->resolveConsoleApplicationClass($containerBuilder);
        if ($consoleApplicationClass === null) {
            $this->registerAutowiredSymfonyConsole($containerBuilder);
            return;
        }
        // add console application alias
        if ($consoleApplicationClass === \RectorPrefix20210510\Symfony\Component\Console\Application::class) {
            return;
        }
        $containerBuilder->setAlias(\RectorPrefix20210510\Symfony\Component\Console\Application::class, $consoleApplicationClass)->setPublic(\true);
        // calls
        // resolve name
        // resolve version
    }
    private function resolveConsoleApplicationClass(\RectorPrefix20210510\Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder) : ?string
    {
        foreach ($containerBuilder->getDefinitions() as $definition) {
            if (!\is_a((string) $definition->getClass(), \RectorPrefix20210510\Symfony\Component\Console\Application::class, \true)) {
                continue;
            }
            return $definition->getClass();
        }
        return null;
    }
    /**
     * Missing console application? add basic one
     */
    private function registerAutowiredSymfonyConsole(\RectorPrefix20210510\Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder) : void
    {
        $containerBuilder->autowire(\RectorPrefix20210510\Symplify\SymplifyKernel\Console\AutowiredConsoleApplication::class, \RectorPrefix20210510\Symplify\SymplifyKernel\Console\AutowiredConsoleApplication::class)->setFactory([new \RectorPrefix20210510\Symfony\Component\DependencyInjection\Reference(\RectorPrefix20210510\Symplify\SymplifyKernel\Console\ConsoleApplicationFactory::class), 'create']);
        $containerBuilder->setAlias(\RectorPrefix20210510\Symfony\Component\Console\Application::class, \RectorPrefix20210510\Symplify\SymplifyKernel\Console\AutowiredConsoleApplication::class)->setPublic(\true);
    }
}
