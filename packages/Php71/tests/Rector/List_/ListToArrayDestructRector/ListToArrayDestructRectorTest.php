<?php

declare(strict_types=1);

namespace Rector\Php71\Tests\Rector\List_\ListToArrayDestructRector;

use Iterator;
use Rector\Php71\Rector\List_\ListToArrayDestructRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ListToArrayDestructRectorTest extends AbstractRectorTestCase
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
        return ListToArrayDestructRector::class;
    }
}
