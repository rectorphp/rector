<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20211020\Symfony\Component\DependencyInjection\LazyProxy\PhpDumper;

use RectorPrefix20211020\Symfony\Component\DependencyInjection\Definition;
/**
 * Null dumper, negates any proxy code generation for any given service definition.
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 *
 * @final
 */
class NullDumper implements \RectorPrefix20211020\Symfony\Component\DependencyInjection\LazyProxy\PhpDumper\DumperInterface
{
    /**
     * {@inheritdoc}
     * @param \Symfony\Component\DependencyInjection\Definition $definition
     */
    public function isProxyCandidate($definition) : bool
    {
        return \false;
    }
    /**
     * {@inheritdoc}
     * @param \Symfony\Component\DependencyInjection\Definition $definition
     * @param string $id
     * @param string $factoryCode
     */
    public function getProxyFactoryCode($definition, $id, $factoryCode) : string
    {
        return '';
    }
    /**
     * {@inheritdoc}
     * @param \Symfony\Component\DependencyInjection\Definition $definition
     */
    public function getProxyCode($definition) : string
    {
        return '';
    }
}
