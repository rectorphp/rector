<?php declare(strict_types=1);

namespace Rector\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class RemoveExcludedRectorsCompilerPass implements CompilerPassInterface
{
    /**
     * @var string
     */
    private const EXCLUDE_RECTORS_KEY = 'exclude_rectors';

    public function process(ContainerBuilder $containerBuilder): void
    {
        $parameterBag = $containerBuilder->getParameterBag();
        if ($parameterBag->has(self::EXCLUDE_RECTORS_KEY) === false) {
            return;
        }

        $excludedRectors = (array) $parameterBag->get(self::EXCLUDE_RECTORS_KEY);

        foreach ($containerBuilder->getDefinitions() as $id => $definition) {
            if ($definition->getClass() === null) {
                continue;
            }

            if (! in_array($definition->getClass(), $excludedRectors, true)) {
                continue;
            }

            $containerBuilder->removeDefinition($id);
        }
    }
}
