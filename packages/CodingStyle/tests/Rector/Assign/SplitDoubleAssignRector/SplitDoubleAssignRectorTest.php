<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\Assign\SplitDoubleAssignRector;

use Iterator;
use Rector\CodingStyle\Rector\Assign\SplitDoubleAssignRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class SplitDoubleAssignRectorTest extends AbstractRectorTestCase
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
        return SplitDoubleAssignRector::class;
    }
}
