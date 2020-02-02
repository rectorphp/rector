<?php

declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\Instanceof_\RemoveDuplicatedInstanceOfRector;

use Iterator;
use Rector\DeadCode\Rector\Instanceof_\RemoveDuplicatedInstanceOfRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RemoveDuplicatedInstanceOfRectorTest extends AbstractRectorTestCase
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
        return RemoveDuplicatedInstanceOfRector::class;
    }
}
