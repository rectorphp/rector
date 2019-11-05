<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\Identical\SimplifyConditionsRector;

use Iterator;
use Rector\CodeQuality\Rector\Identical\SimplifyConditionsRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class SimplifyConditionsRectorTest extends AbstractRectorTestCase
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
        return SimplifyConditionsRector::class;
    }
}
