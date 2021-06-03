<?php

declare (strict_types=1);
namespace Rector\Core\DependencyInjection\CompilerPass;

use Rector\Core\Contract\Rector\RectorInterface;
use Rector\Core\Exception\ShouldNotHappenException;
use RectorPrefix20210603\Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use RectorPrefix20210603\Symfony\Component\DependencyInjection\ContainerBuilder;
final class VerifyRectorServiceExistsCompilerPass implements \RectorPrefix20210603\Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface
{
    public function process(\RectorPrefix20210603\Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder) : void
    {
        foreach ($containerBuilder->getDefinitions() as $definition) {
            $class = $definition->getClass();
            if ($class === null) {
                continue;
            }
            if (\substr_compare($class, 'Rector', -\strlen('Rector')) !== 0) {
                continue;
            }
            if (!\is_a($class, \Rector\Core\Contract\Rector\RectorInterface::class, \true)) {
                throw new \Rector\Core\Exception\ShouldNotHappenException(\sprintf('Rector rule "%s" not found, please verify that the rule exists', $class));
            }
        }
    }
}
