<?php declare(strict_types=1);

namespace Rector\YamlRector\DependencyInjection\CompilerPass;

use Rector\YamlRector\Contract\YamlRectorInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @todo use AutowireDefaultCompilerPass instead
 */
final class AutowireYamlRectorCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $containerBuilder): void
    {
        foreach ($containerBuilder->getDefinitions() as $definition) {
            if ($definition->getClass() === null) {
                continue;
            }

            if (! is_a($definition->getClass(), YamlRectorInterface::class, true)) {
                continue;
            }

            $definition->setAutowired(true);
        }
    }
}
