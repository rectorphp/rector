<?php

declare (strict_types=1);
namespace Rector\Core\DependencyInjection\CompilerPass;

use Rector\Core\Contract\Rector\RectorInterface;
use RectorPrefix202212\Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use RectorPrefix202212\Symfony\Component\DependencyInjection\ContainerBuilder;
final class AutowireRectorCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $containerBuilder) : void
    {
        $definitions = $containerBuilder->getDefinitions();
        foreach ($definitions as $definition) {
            if (!\is_a((string) $definition->getClass(), RectorInterface::class, \true)) {
                continue;
            }
            $definition->setAutowired(\true);
        }
    }
}
