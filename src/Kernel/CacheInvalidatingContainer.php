<?php

declare (strict_types=1);
namespace Rector\Core\Kernel;

use Rector\Core\Exception\Cache\StaleContainerCacheException;
use RectorPrefix202305\Symfony\Component\DependencyInjection\ContainerInterface;
final class CacheInvalidatingContainer implements ContainerInterface
{
    /**
     * @readonly
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $wrapped;
    public function __construct(ContainerInterface $wrapped)
    {
        $this->wrapped = $wrapped;
    }
    public function set(string $id, ?object $service) : void
    {
        $this->wrapped->set($id, $service);
    }
    public function get(string $id, int $invalidBehavior = self::EXCEPTION_ON_INVALID_REFERENCE) : ?object
    {
        try {
            return $this->wrapped->get($id, $invalidBehavior);
        } catch (\Throwable $throwable) {
            // clear compiled container cache, to trigger re-discovery
            \Rector\Core\Kernel\RectorKernel::clearCache();
            throw new StaleContainerCacheException('Container cache is outdated and was cleared. please re-run the command.', 0, $throwable);
        }
    }
    public function has(string $id) : bool
    {
        return $this->wrapped->has($id);
    }
    public function initialized(string $id) : bool
    {
        return $this->wrapped->initialized($id);
    }
    /**
     * @return array<mixed>|bool|float|int|string|\UnitEnum|null
     */
    public function getParameter(string $name)
    {
        return $this->wrapped->getParameter($name);
    }
    public function hasParameter(string $name) : bool
    {
        return $this->wrapped->hasParameter($name);
    }
    /**
     * @param \UnitEnum|float|array<mixed>|bool|int|string|null $value
     */
    public function setParameter(string $name, $value) : void
    {
        $this->wrapped->setParameter($name, $value);
    }
}
