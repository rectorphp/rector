<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\FuncCall\ConsistentImplodeRector;

use Iterator;
use Rector\CodingStyle\Rector\FuncCall\ConsistentImplodeRector;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;

final class ConsistentImplodeRectorTest extends AbstractRectorTestCase
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
        return ConsistentImplodeRector::class;
    }
}
