<?php

declare(strict_types=1);

namespace Rector\SOLID\Tests\Rector\Class_\FinalizeClassesWithoutChildrenRector;

use Iterator;
use Rector\SOLID\Rector\Class_\FinalizeClassesWithoutChildrenRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class FinalizeClassesWithoutChildrenRectorTest extends AbstractRectorTestCase
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
        return FinalizeClassesWithoutChildrenRector::class;
    }
}
