<?php

declare(strict_types=1);

namespace Rector\Tests\PHPUnit\Rector\ClassMethod\AddDoesNotPerformAssertionToNonAssertingTestRector\Fixture;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Rector\PHPUnit\Tests\PHPUnit60\Rector\ClassMethod\AddDoesNotPerformAssertionToNonAssertingTestRector\Source\CachedAdapter;
use Rector\PHPUnit\Tests\PHPUnit60\Rector\ClassMethod\AddDoesNotPerformAssertionToNonAssertingTestRector\Source\CacheInterface;

final class CachedAdapterTest extends TestCase
{
    /** @var CacheInterface&MockObject */
    private MockObject $cache;
    private CachedAdapter $cachedAdapter;

    protected function setUp(): void
    {
        $this->cache = $this->createMock(CacheInterface::class);
        $this->cachedAdapter = new CachedAdapter($this->cache);
    }

    public function testDelete(): void
    {
        $this->cache
            ->expects(self::never())
            ->method('has');

        $this->cachedAdapter->delete('test');
    }
}
