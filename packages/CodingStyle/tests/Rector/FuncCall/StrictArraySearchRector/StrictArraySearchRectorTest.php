<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\FuncCall\StrictArraySearchRector;

use Iterator;
use Rector\CodingStyle\Rector\FuncCall\StrictArraySearchRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class StrictArraySearchRectorTest extends AbstractRectorTestCase
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
        return StrictArraySearchRector::class;
    }
}
