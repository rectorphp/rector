<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\SpecificAssertContainsRector\Fixture;

use PHPUnit\Framework\TestCase;

final class SkipIterableGet extends TestCase
{
    public function test()
    {
        $items = $this->getItems([]);
        $this->assertContains('foo', $items[1]);
    }

    public function getItems(array $array): array
    {
        return $array;
    }
}

