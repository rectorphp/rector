<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\Foreach_\ForeachToInArrayRector;

use Iterator;
use Rector\CodeQuality\Rector\Foreach_\ForeachToInArrayRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ForeachToInArrayRectorTest extends AbstractRectorTestCase
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
        return ForeachToInArrayRector::class;
    }
}
