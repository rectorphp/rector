<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix202301\Symfony\Component\DependencyInjection\Loader\Configurator\Traits;

trait PropertyTrait
{
    /**
     * Sets a specific property.
     *
     * @return $this
     * @param mixed $value
     */
    public final function property(string $name, $value)
    {
        $this->definition->setProperty($name, static::processValue($value, \true));
        return $this;
    }
}
