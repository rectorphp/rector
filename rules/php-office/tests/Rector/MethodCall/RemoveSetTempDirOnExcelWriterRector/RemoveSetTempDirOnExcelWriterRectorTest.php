<?php

declare(strict_types=1);

namespace Rector\PHPOffice\Tests\Rector\MethodCall\RemoveSetTempDirOnExcelWriterRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\PHPOffice\Rector\MethodCall\RemoveSetTempDirOnExcelWriterRector;

final class RemoveSetTempDirOnExcelWriterRectorTest extends AbstractRectorTestCase
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
        return RemoveSetTempDirOnExcelWriterRector::class;
    }
}
