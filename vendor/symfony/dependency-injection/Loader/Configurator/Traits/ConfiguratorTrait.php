<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix202304\Symfony\Component\DependencyInjection\Loader\Configurator\Traits;

use RectorPrefix202304\Symfony\Component\DependencyInjection\Loader\Configurator\ReferenceConfigurator;
trait ConfiguratorTrait
{
    /**
     * Sets a configurator to call after the service is fully initialized.
     *
     * @return $this
     * @param string|mixed[]|\Symfony\Component\DependencyInjection\Loader\Configurator\ReferenceConfigurator $configurator
     */
    public final function configurator($configurator)
    {
        $this->definition->setConfigurator(static::processValue($configurator, \true));
        return $this;
    }
}
