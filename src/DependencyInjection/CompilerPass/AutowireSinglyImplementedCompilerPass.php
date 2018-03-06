<?php declare(strict_types=1);

namespace Rector\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Inspired by https://github.com/symfony/symfony/pull/25282/files
 * but not only for PSR-4, but also other manual registration
 */
final class AutowireSinglyImplementedCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $containerBuilder): void
    {
        $singlyImplemented = $this->collectSinglyImplementedInterfaces($containerBuilder);

        foreach ($singlyImplemented as $interface => $class) {
            $containerBuilder->setAlias($interface, $class);
        }
    }

    /**
     * @return string[]
     */
    private function collectSinglyImplementedInterfaces(ContainerBuilder $containerBuilder): array
    {
        $singlyImplemented = [];

        foreach ($containerBuilder->getDefinitions() as $definition) {
            $class = $definition->getClass();

            foreach (class_implements($class, false) as $interface) {
                $singlyImplemented[$interface] = isset($singlyImplemented[$interface]) ? false : $class;
            }
        }

        return array_filter($singlyImplemented);
    }
}
