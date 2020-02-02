<?php

declare(strict_types=1);

namespace Rector\Php73\Tests\Rector\FuncCall\RemoveMissingCompactVariableRector;

use Iterator;
use Rector\Php73\Rector\FuncCall\RemoveMissingCompactVariableRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RemoveMissingCompactVariableRectorTest extends AbstractRectorTestCase
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
        return RemoveMissingCompactVariableRector::class;
    }
}
