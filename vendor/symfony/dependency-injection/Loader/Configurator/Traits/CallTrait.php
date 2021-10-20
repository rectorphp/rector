<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20211020\Symfony\Component\DependencyInjection\Loader\Configurator\Traits;

use RectorPrefix20211020\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
trait CallTrait
{
    /**
     * Adds a method to call after service initialization.
     *
     * @param string $method       The method name to call
     * @param array  $arguments    An array of arguments to pass to the method call
     * @param bool   $returnsClone Whether the call returns the service instance or not
     *
     * @return $this
     *
     * @throws InvalidArgumentException on empty $method param
     */
    public final function call($method, $arguments = [], $returnsClone = \false) : self
    {
        $this->definition->addMethodCall($method, static::processValue($arguments, \true), $returnsClone);
        return $this;
    }
}
