<?php

declare (strict_types=1);
namespace Rector\Core\Kernel;

use Rector\Core\Exception\Cache\StaleContainerCacheException;
use RectorPrefix202305\Symfony\Component\DependencyInjection\ContainerInterface;
use Throwable;
use UnitEnum;
final class CacheInvalidatingContainer implements ContainerInterface
{
    /**
     * @readonly
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
    public function set(string $id, ?object $service) : void
    {
        $this->container->set($id, $service);
    }
    public function get(string $id, int $invalidBehavior = self::EXCEPTION_ON_INVALID_REFERENCE) : ?object
    {
        try {
            return $this->container->get($id, $invalidBehavior);
        } catch (Throwable $throwable) {
            // clear compiled container cache, to trigger re-discovery
            \Rector\Core\Kernel\RectorKernel::clearCache();
            throw new StaleContainerCacheException('Container cache is outdated and was cleared. please re-run the command.', 0, $throwable);
        }
    }
    public function has(string $id) : bool
    {
        return $this->container->has($id);
    }
    public function initialized(string $id) : bool
    {
        return $this->container->initialized($id);
    }
    /**
     * @return array<mixed>|bool|float|int|string|UnitEnum|null
     */
    public function getParameter(string $name)
    {
        return $this->container->getParameter($name);
    }
    public function hasParameter(string $name) : bool
    {
        return $this->container->hasParameter($name);
    }
    /**
     * @param UnitEnum|float|array<mixed>|bool|int|string|null $value
     */
    public function setParameter(string $name, $value) : void
    {
        $this->container->setParameter($name, $value);
    }
}
