<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20211123\Symfony\Contracts\Tests\Cache;

use RectorPrefix20211123\PHPUnit\Framework\TestCase;
use RectorPrefix20211123\Psr\Cache\CacheItemInterface;
use RectorPrefix20211123\Psr\Cache\CacheItemPoolInterface;
use RectorPrefix20211123\Symfony\Contracts\Cache\CacheTrait;
/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class CacheTraitTest extends \RectorPrefix20211123\PHPUnit\Framework\TestCase
{
    public function testSave()
    {
        $item = $this->createMock(\RectorPrefix20211123\Psr\Cache\CacheItemInterface::class);
        $item->method('set')->willReturn($item);
        $item->method('isHit')->willReturn(\false);
        $item->expects($this->once())->method('set')->with('computed data');
        $cache = $this->getMockBuilder(\RectorPrefix20211123\Symfony\Contracts\Tests\Cache\TestPool::class)->setMethods(['getItem', 'save'])->getMock();
        $cache->expects($this->once())->method('getItem')->with('key')->willReturn($item);
        $cache->expects($this->once())->method('save');
        $callback = function (\RectorPrefix20211123\Psr\Cache\CacheItemInterface $item) {
            return 'computed data';
        };
        $cache->get('key', $callback);
    }
    public function testNoCallbackCallOnHit()
    {
        $item = $this->createMock(\RectorPrefix20211123\Psr\Cache\CacheItemInterface::class);
        $item->method('isHit')->willReturn(\true);
        $item->expects($this->never())->method('set');
        $cache = $this->getMockBuilder(\RectorPrefix20211123\Symfony\Contracts\Tests\Cache\TestPool::class)->setMethods(['getItem', 'save'])->getMock();
        $cache->expects($this->once())->method('getItem')->with('key')->willReturn($item);
        $cache->expects($this->never())->method('save');
        $callback = function (\RectorPrefix20211123\Psr\Cache\CacheItemInterface $item) {
            $this->assertTrue(\false, 'This code should never be reached');
        };
        $cache->get('key', $callback);
    }
    public function testRecomputeOnBetaInf()
    {
        $item = $this->createMock(\RectorPrefix20211123\Psr\Cache\CacheItemInterface::class);
        $item->method('set')->willReturn($item);
        $item->method('isHit')->willReturn(\true);
        $item->expects($this->once())->method('set')->with('computed data');
        $cache = $this->getMockBuilder(\RectorPrefix20211123\Symfony\Contracts\Tests\Cache\TestPool::class)->setMethods(['getItem', 'save'])->getMock();
        $cache->expects($this->once())->method('getItem')->with('key')->willReturn($item);
        $cache->expects($this->once())->method('save');
        $callback = function (\RectorPrefix20211123\Psr\Cache\CacheItemInterface $item) {
            return 'computed data';
        };
        $cache->get('key', $callback, \INF);
    }
    public function testExceptionOnNegativeBeta()
    {
        $cache = $this->getMockBuilder(\RectorPrefix20211123\Symfony\Contracts\Tests\Cache\TestPool::class)->setMethods(['getItem', 'save'])->getMock();
        $callback = function (\RectorPrefix20211123\Psr\Cache\CacheItemInterface $item) {
            return 'computed data';
        };
        $this->expectException(\InvalidArgumentException::class);
        $cache->get('key', $callback, -2);
    }
}
class TestPool implements \RectorPrefix20211123\Psr\Cache\CacheItemPoolInterface
{
    use CacheTrait;
    public function hasItem($key) : bool
    {
    }
    public function deleteItem($key) : bool
    {
    }
    /**
     * @param mixed[] $keys
     */
    public function deleteItems($keys = []) : bool
    {
    }
    public function getItem($key) : \RectorPrefix20211123\Psr\Cache\CacheItemInterface
    {
    }
    /**
     * @param mixed[] $key
     */
    public function getItems($key = []) : iterable
    {
    }
    /**
     * @param \Psr\Cache\CacheItemInterface $item
     */
    public function saveDeferred($item) : bool
    {
    }
    /**
     * @param \Psr\Cache\CacheItemInterface $item
     */
    public function save($item) : bool
    {
    }
    public function commit() : bool
    {
    }
    public function clear() : bool
    {
    }
}
