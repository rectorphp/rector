<?php

declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\Array_\RemoveDuplicatedArrayKeyRector;

use Iterator;
use Rector\DeadCode\Rector\Array_\RemoveDuplicatedArrayKeyRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RemoveDuplicatedArrayKeyRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return RemoveDuplicatedArrayKeyRector::class;
    }
}
