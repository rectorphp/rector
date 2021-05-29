<?php

declare(strict_types=1);

namespace Rector\Core\DependencyInjection\CompilerPass;

use Rector\Core\Contract\Rector\RectorInterface;
use Rector\Core\Exception\ShouldNotHappenException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class VerifyRectorServiceExistsCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $containerBuilder): void
    {
        foreach ($containerBuilder->getDefinitions() as $definition) {
            $class = $definition->getClass();
            if ($class === null) {
                continue;
            }

            if (! \str_ends_with($class, 'Rector')) {
                continue;
            }

            if (! is_a($class, RectorInterface::class, true)) {
                throw new ShouldNotHappenException(
                    sprintf('Rector rule "%s" not found, please verify that the rule exists', $class)
                );
            }
        }
    }
}
