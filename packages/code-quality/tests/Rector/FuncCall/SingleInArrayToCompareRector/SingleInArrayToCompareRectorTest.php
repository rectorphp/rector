<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\FuncCall\SingleInArrayToCompareRector;

use Iterator;
use Rector\CodeQuality\Rector\FuncCall\SingleInArrayToCompareRector;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;

final class SingleInArrayToCompareRectorTest extends AbstractRectorTestCase
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
        return SingleInArrayToCompareRector::class;
    }
}
