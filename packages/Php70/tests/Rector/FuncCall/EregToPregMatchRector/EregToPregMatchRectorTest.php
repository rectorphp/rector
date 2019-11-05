<?php

declare(strict_types=1);

namespace Rector\Php70\Tests\Rector\FuncCall\EregToPregMatchRector;

use Iterator;
use Rector\Php70\Rector\FuncCall\EregToPregMatchRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class EregToPregMatchRectorTest extends AbstractRectorTestCase
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
        return EregToPregMatchRector::class;
    }
}
