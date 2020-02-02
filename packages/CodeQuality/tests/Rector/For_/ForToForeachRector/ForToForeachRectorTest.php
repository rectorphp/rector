<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\For_\ForToForeachRector;

use Iterator;
use Rector\CodeQuality\Rector\For_\ForToForeachRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ForToForeachRectorTest extends AbstractRectorTestCase
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
        return ForToForeachRector::class;
    }
}
