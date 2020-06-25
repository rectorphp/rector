<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\FuncCall\SimplifyInArrayValuesRector;

use Iterator;
use Rector\CodeQuality\Rector\FuncCall\SimplifyInArrayValuesRector;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class SimplifyInArrayValuesRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $file): void
    {
        $this->doTestFileInfo($file);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return SimplifyInArrayValuesRector::class;
    }
}
