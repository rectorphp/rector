<?php

declare(strict_types=1);

namespace Rector\DependencyInjection\CompilerPass;

use Rector\Configuration\Option;
use Rector\Contract\Rector\RectorInterface;
use Rector\Exception\ShouldNotHappenException;
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
        if (! $parameterBag->has(self::EXCLUDE_RECTORS_KEY)) {
            return;
        }

        $excludedRectors = (array) $parameterBag->get(self::EXCLUDE_RECTORS_KEY);

        $this->ensureClassesExistsAndAreRectors($excludedRectors);

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

    /**
     * @param string[] $excludedRectors
     */
    private function ensureClassesExistsAndAreRectors(array $excludedRectors): void
    {
        foreach ($excludedRectors as $excludedRector) {
            $this->ensureClassExists($excludedRector);
            $this->ensureIsRectorClass($excludedRector);
        }
    }

    private function ensureClassExists(string $excludedRector): void
    {
        if (class_exists($excludedRector)) {
            return;
        }

        throw new ShouldNotHappenException(sprintf(
            'Class "%s" defined in "parameters > %s" was not found ',
            $excludedRector,
            Option::EXCLUDE_RECTORS_PARAMETER
        ));
    }

    private function ensureIsRectorClass(string $excludedRector): void
    {
        if (is_a($excludedRector, RectorInterface::class, true)) {
            return;
        }

        throw new ShouldNotHappenException(sprintf(
            'Class "%s" defined in "parameters > %s" is not a Rector rule = does not implement "%s" ',
            $excludedRector,
            Option::EXCLUDE_RECTORS_PARAMETER,
            RectorInterface::class
        ));
    }
}
