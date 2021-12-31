<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20211231\Symfony\Component\DependencyInjection\Compiler;

use RectorPrefix20211231\Symfony\Component\DependencyInjection\Alias;
use RectorPrefix20211231\Symfony\Component\DependencyInjection\ContainerBuilder;
use RectorPrefix20211231\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
/**
 * Sets a service to be an alias of another one, given a format pattern.
 */
class AutoAliasServicePass implements \RectorPrefix20211231\Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface
{
    private $privateAliases = [];
    /**
     * {@inheritdoc}
     */
    public function process(\RectorPrefix20211231\Symfony\Component\DependencyInjection\ContainerBuilder $container)
    {
        foreach ($container->findTaggedServiceIds('auto_alias') as $serviceId => $tags) {
            foreach ($tags as $tag) {
                if (!isset($tag['format'])) {
                    throw new \RectorPrefix20211231\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException(\sprintf('Missing tag information "format" on auto_alias service "%s".', $serviceId));
                }
                $aliasId = $container->getParameterBag()->resolveValue($tag['format']);
                if ($container->hasDefinition($aliasId) || $container->hasAlias($aliasId)) {
                    $alias = new \RectorPrefix20211231\Symfony\Component\DependencyInjection\Alias($aliasId, $container->getDefinition($serviceId)->isPublic());
                    $container->setAlias($serviceId, $alias);
                    if (!$alias->isPublic()) {
                        $alias->setPublic(\true);
                        $this->privateAliases[] = $alias;
                    }
                }
            }
        }
    }
    /**
     * @internal to be removed in Symfony 6.0
     */
    public function getPrivateAliases() : array
    {
        $privateAliases = $this->privateAliases;
        $this->privateAliases = [];
        return $privateAliases;
    }
}
