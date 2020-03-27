<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\FuncCall\ArrayKeysAndInArrayToIssetRector;

use Iterator;
use Rector\CodeQuality\Rector\FuncCall\ArrayKeysAndInArrayToIssetRector;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;

final class ArrayKeysAndInArrayToIssetRectorTest extends AbstractRectorTestCase
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
        return ArrayKeysAndInArrayToIssetRector::class;
    }
}
