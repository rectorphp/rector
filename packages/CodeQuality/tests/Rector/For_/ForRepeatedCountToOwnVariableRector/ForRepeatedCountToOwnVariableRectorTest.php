<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\For_\ForRepeatedCountToOwnVariableRector;

use Iterator;
use Rector\CodeQuality\Rector\For_\ForRepeatedCountToOwnVariableRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ForRepeatedCountToOwnVariableRectorTest extends AbstractRectorTestCase
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
        return ForRepeatedCountToOwnVariableRector::class;
    }
}
