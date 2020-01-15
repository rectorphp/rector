<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\If_\SplitIfsRector;

use Iterator;
use Rector\CodeQuality\Rector\If_\SplitIfsRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class SplitIfsRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    public function provideDataForTest(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return SplitIfsRector::class;
    }
}
