<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix202208\Symfony\Component\DependencyInjection\Loader\Configurator\Traits;

use RectorPrefix202208\Symfony\Component\DependencyInjection\ContainerInterface;
use RectorPrefix202208\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
trait DecorateTrait
{
    /**
     * Sets the service that this service is decorating.
     *
     * @param string|null $id The decorated service id, use null to remove decoration
     *
     * @return $this
     *
     * @throws InvalidArgumentException in case the decorated service id and the new decorated service id are equals
     */
    public final function decorate(?string $id, string $renamedId = null, int $priority = 0, int $invalidBehavior = ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE)
    {
        $this->definition->setDecoratedService($id, $renamedId, $priority, $invalidBehavior);
        return $this;
    }
}
