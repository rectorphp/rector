<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\FuncCall\RemoveSoleValueSprintfRector;

use Iterator;
use Rector\CodeQuality\Rector\FuncCall\RemoveSoleValueSprintfRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RemoveSoleValueSprintfRectorTest extends AbstractRectorTestCase
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
        return RemoveSoleValueSprintfRector::class;
    }
}
