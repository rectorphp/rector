<?php declare(strict_types=1);

namespace Rector\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class AutowireDefaultCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $containerBuilder): void
    {
        foreach ($containerBuilder->getDefinitions() as $definition) {
            if ($definition->getClass() === null) {
                continue;
            }

            $definition->setAutowired(true);
        }
    }
}
