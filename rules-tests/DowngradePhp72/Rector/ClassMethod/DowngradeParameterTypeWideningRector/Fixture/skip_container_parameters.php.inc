<?php

namespace Rector\Tests\DowngradePhp72\Rector\ClassMethod\DowngradeParameterTypeWideningRector\Fixture;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

final class SkipContainerParameters implements ContainerInterface
{
    public function set(string $id, $service)
    {
    }

    public function get(string $id, int $invalidBehavior = self::EXCEPTION_ON_INVALID_REFERENCE)
    {
    }

    public function has(string $id)
    {
    }

    public function initialized(string $id)
    {
        // TODO: Implement initialized() method.
    }

    public function getParameter(string $name)
    {
    }

    public function hasParameter(string $name)
    {
    }

    public function setParameter(string $name, $value)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp72\Rector\ClassMethod\DowngradeParameterTypeWideningRector\Fixture;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

final class SkipContainerParameters implements ContainerInterface
{
    /**
     * @param string $id
     */
    public function set($id, $service)
    {
    }

    /**
     * @param string $id
     * @param int $invalidBehavior
     */
    public function get($id, $invalidBehavior = self::EXCEPTION_ON_INVALID_REFERENCE)
    {
    }

    /**
     * @param string $id
     */
    public function has($id)
    {
    }

    /**
     * @param string $id
     */
    public function initialized($id)
    {
        // TODO: Implement initialized() method.
    }

    public function getParameter(string $name)
    {
    }

    public function hasParameter(string $name)
    {
    }

    public function setParameter(string $name, $value)
    {
    }
}

?>
