<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\FuncCall\ArrayKeysAndInArrayToArrayKeyExistsRector;

use Iterator;
use Rector\CodeQuality\Rector\FuncCall\ArrayKeysAndInArrayToArrayKeyExistsRector;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;

final class ArrayKeysAndInArrayToArrayKeyExistsRectorTest extends AbstractRectorTestCase
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
        return ArrayKeysAndInArrayToArrayKeyExistsRector::class;
    }
}
