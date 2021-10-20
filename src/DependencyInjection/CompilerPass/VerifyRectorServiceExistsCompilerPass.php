<?php

declare (strict_types=1);
namespace Rector\Core\DependencyInjection\CompilerPass;

use Rector\Core\Contract\Rector\RectorInterface;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use RectorPrefix20211020\Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use RectorPrefix20211020\Symfony\Component\DependencyInjection\ContainerBuilder;
final class VerifyRectorServiceExistsCompilerPass implements \RectorPrefix20211020\Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface
{
    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder
     */
    public function process($containerBuilder) : void
    {
        foreach ($containerBuilder->getDefinitions() as $definition) {
            $class = $definition->getClass();
            if ($class === null) {
                continue;
            }
            if (\substr_compare($class, 'Rector', -\strlen('Rector')) !== 0) {
                continue;
            }
            if (!\class_exists($class)) {
                throw new \Rector\Core\Exception\ShouldNotHappenException(\sprintf('Rector rule "%s" not found, please verify that the class exists and is autoloadable.', $class));
            }
            if (!\is_a($class, \Rector\Core\Contract\Rector\RectorInterface::class, \true)) {
                throw new \Rector\Core\Exception\ShouldNotHappenException(\sprintf('Rector rule "%s" should extend "%s" or implement "%s".', $class, \Rector\Core\Rector\AbstractRector::class, \Rector\Core\Contract\Rector\RectorInterface::class));
            }
        }
    }
}
