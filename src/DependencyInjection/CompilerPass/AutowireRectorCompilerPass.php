<?php declare(strict_types=1);

namespace Rector\DependencyInjection\CompilerPass;

use Rector\Contract\Rector\RectorInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class AutowireRectorCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $containerBuilder): void
    {
        foreach ($containerBuilder->getDefinitions() as $definition) {
            if (! is_a($definition->getClass(), RectorInterface::class, true)) {
                continue;
            }

            $definition->setAutowired(true);
        }
    }
}
