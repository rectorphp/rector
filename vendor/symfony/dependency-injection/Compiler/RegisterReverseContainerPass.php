<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20220501\Symfony\Component\DependencyInjection\Compiler;

use RectorPrefix20220501\Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument;
use RectorPrefix20220501\Symfony\Component\DependencyInjection\ContainerBuilder;
use RectorPrefix20220501\Symfony\Component\DependencyInjection\ContainerInterface;
use RectorPrefix20220501\Symfony\Component\DependencyInjection\Definition;
use RectorPrefix20220501\Symfony\Component\DependencyInjection\Reference;
/**
 * @author Nicolas Grekas <p@tchwork.com>
 */
class RegisterReverseContainerPass implements \RectorPrefix20220501\Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface
{
    /**
     * @var bool
     */
    private $beforeRemoving;
    public function __construct(bool $beforeRemoving)
    {
        $this->beforeRemoving = $beforeRemoving;
    }
    public function process(\RectorPrefix20220501\Symfony\Component\DependencyInjection\ContainerBuilder $container)
    {
        if (!$container->hasDefinition('reverse_container')) {
            return;
        }
        $refType = $this->beforeRemoving ? \RectorPrefix20220501\Symfony\Component\DependencyInjection\ContainerInterface::IGNORE_ON_UNINITIALIZED_REFERENCE : \RectorPrefix20220501\Symfony\Component\DependencyInjection\ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE;
        $services = [];
        foreach ($container->findTaggedServiceIds('container.reversible') as $id => $tags) {
            $services[$id] = new \RectorPrefix20220501\Symfony\Component\DependencyInjection\Reference($id, $refType);
        }
        if ($this->beforeRemoving) {
            // prevent inlining of the reverse container
            $services['reverse_container'] = new \RectorPrefix20220501\Symfony\Component\DependencyInjection\Reference('reverse_container', $refType);
        }
        $locator = $container->getDefinition('reverse_container')->getArgument(1);
        if ($locator instanceof \RectorPrefix20220501\Symfony\Component\DependencyInjection\Reference) {
            $locator = $container->getDefinition((string) $locator);
        }
        if ($locator instanceof \RectorPrefix20220501\Symfony\Component\DependencyInjection\Definition) {
            foreach ($services as $id => $ref) {
                $services[$id] = new \RectorPrefix20220501\Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument($ref);
            }
            $locator->replaceArgument(0, $services);
        } else {
            $locator->setValues($services);
        }
    }
}
