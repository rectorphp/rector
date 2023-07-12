<?php

declare (strict_types=1);
namespace Rector\PHPUnit\Tests\PHPUnit60\Rector\ClassMethod\AddDoesNotPerformAssertionToNonAssertingTestRector\Source;

final class CachedAdapter
{
    /**
     * @var \Rector\PHPUnit\Tests\PHPUnit60\Rector\ClassMethod\AddDoesNotPerformAssertionToNonAssertingTestRector\Source\CacheInterface
     */
    private $cache;
    public function __construct(\Rector\PHPUnit\Tests\PHPUnit60\Rector\ClassMethod\AddDoesNotPerformAssertionToNonAssertingTestRector\Source\CacheInterface $cache)
    {
        $this->cache = $cache;
    }
    public function delete($key) : bool
    {
        return $this->cache->delete($key);
    }
}
