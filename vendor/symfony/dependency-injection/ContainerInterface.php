<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20211020\Symfony\Component\DependencyInjection;

use RectorPrefix20211020\Psr\Container\ContainerInterface as PsrContainerInterface;
use RectorPrefix20211020\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use RectorPrefix20211020\Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use RectorPrefix20211020\Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
/**
 * ContainerInterface is the interface implemented by service container classes.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
interface ContainerInterface extends \RectorPrefix20211020\Psr\Container\ContainerInterface
{
    public const RUNTIME_EXCEPTION_ON_INVALID_REFERENCE = 0;
    public const EXCEPTION_ON_INVALID_REFERENCE = 1;
    public const NULL_ON_INVALID_REFERENCE = 2;
    public const IGNORE_ON_INVALID_REFERENCE = 3;
    public const IGNORE_ON_UNINITIALIZED_REFERENCE = 4;
    /**
     * Sets a service.
     * @param object|null $service
     * @param string $id
     */
    public function set($id, $service);
    /**
     * Gets a service.
     *
     * @param string $id              The service identifier
     * @param int    $invalidBehavior The behavior when the service does not exist
     *
     * @return object|null The associated service
     *
     * @throws ServiceCircularReferenceException When a circular reference is detected
     * @throws ServiceNotFoundException          When the service is not defined
     *
     * @see Reference
     */
    public function get($id, $invalidBehavior = self::EXCEPTION_ON_INVALID_REFERENCE);
    /**
     * @return bool true if the service is defined, false otherwise
     * @param string $id
     */
    public function has($id);
    /**
     * Check for whether or not a service has been initialized.
     *
     * @return bool true if the service has been initialized, false otherwise
     * @param string $id
     */
    public function initialized($id);
    /**
     * @return array|bool|string|int|float|null
     *
     * @throws InvalidArgumentException if the parameter is not defined
     */
    public function getParameter(string $name);
    /**
     * @return bool
     */
    public function hasParameter(string $name);
    /**
     * Sets a parameter.
     *
     * @param string                           $name  The parameter name
     * @param array|bool|string|int|float|null $value The parameter value
     */
    public function setParameter(string $name, $value);
}
