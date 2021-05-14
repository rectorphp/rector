<?php

declare (strict_types=1);
namespace Rector\Core\DependencyInjection\CompilerPass;

use RectorPrefix20210514\Nette\Utils\Strings;
use Rector\Core\Contract\Rector\RectorInterface;
use Rector\Core\Exception\ShouldNotHappenException;
use RectorPrefix20210514\Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use RectorPrefix20210514\Symfony\Component\DependencyInjection\ContainerBuilder;
final class VerifyRectorServiceExistsCompilerPass implements \RectorPrefix20210514\Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface
{
    public function process(\RectorPrefix20210514\Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder) : void
    {
        foreach ($containerBuilder->getDefinitions() as $definition) {
            $class = $definition->getClass();
            if ($class === null) {
                continue;
            }
            if (!\RectorPrefix20210514\Nette\Utils\Strings::endsWith($class, 'Rector')) {
                continue;
            }
            if (!\is_a($class, \Rector\Core\Contract\Rector\RectorInterface::class, \true)) {
                throw new \Rector\Core\Exception\ShouldNotHappenException(\sprintf('Rector rule "%s" not found, please verify that the rule exists', $class));
            }
        }
    }
}
