<?php

declare (strict_types=1);
namespace Rector\Core\DependencyInjection\CompilerPass;

use Rector\Core\Configuration\Option;
use Rector\Core\Contract\Rector\RectorInterface;
use RectorPrefix20220501\Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use RectorPrefix20220501\Symfony\Component\DependencyInjection\ContainerBuilder;
/**
 * This compiler pass removed Rectors skipped in `SKIP` parameters.
 * It uses Skipper from Symplify - https://github.com/symplify/skipper
 */
final class RemoveSkippedRectorsCompilerPass implements \RectorPrefix20220501\Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface
{
    public function process(\RectorPrefix20220501\Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder) : void
    {
        $skippedRectorClasses = $this->resolveSkippedRectorClasses($containerBuilder);
        foreach ($containerBuilder->getDefinitions() as $id => $definition) {
            if ($definition->getClass() === null) {
                continue;
            }
            if (!\in_array($definition->getClass(), $skippedRectorClasses, \true)) {
                continue;
            }
            $containerBuilder->removeDefinition($id);
        }
    }
    /**
     * @return string[]
     */
    private function resolveSkippedRectorClasses(\RectorPrefix20220501\Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder) : array
    {
        $skipParameters = (array) $containerBuilder->getParameter(\Rector\Core\Configuration\Option::SKIP);
        return \array_filter($skipParameters, function ($element) : bool {
            return $this->isRectorClass($element);
        });
    }
    /**
     * @param mixed $element
     */
    private function isRectorClass($element) : bool
    {
        if (!\is_string($element)) {
            return \false;
        }
        return \is_a($element, \Rector\Core\Contract\Rector\RectorInterface::class, \true);
    }
}
