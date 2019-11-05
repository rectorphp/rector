<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\FuncCall\VersionCompareFuncCallToConstantRector;

use Iterator;
use Rector\CodingStyle\Rector\FuncCall\VersionCompareFuncCallToConstantRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class VersionCompareFuncCallToConstantRectorTest extends AbstractRectorTestCase
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
        return VersionCompareFuncCallToConstantRector::class;
    }
}
