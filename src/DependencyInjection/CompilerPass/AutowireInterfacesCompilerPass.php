<?php declare(strict_types=1);

namespace Rector\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class AutowireInterfacesCompilerPass implements CompilerPassInterface
{
    /**
     * @var array|string[]
     */
    private $typesToAutowire = [];

    /**
     * @param string[] $typesToAutowire
     */
    public function __construct(array $typesToAutowire)
    {
        $this->typesToAutowire = $typesToAutowire;
    }

    public function process(ContainerBuilder $containerBuilder): void
    {
        foreach ($containerBuilder->getDefinitions() as $definition) {
            foreach ($this->typesToAutowire as $typeToAutowire) {
                if (is_a((string) $definition->getClass(), $typeToAutowire, true)) {
                    $definition->setAutowired(true);
                    continue 2;
                }
            }
        }
    }
}
