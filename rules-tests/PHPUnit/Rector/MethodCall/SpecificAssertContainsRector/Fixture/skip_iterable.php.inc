<?php

namespace Rector\Tests\PHPUnit\Rector\MethodCall\SpecificAssertContainsRector\Fixture;

use PHPUnit\Framework\TestCase;

final class SkipIterable extends TestCase
{
    public function test(array $items)
    {
        $this->assertContains('foo', $items);
    }
}
