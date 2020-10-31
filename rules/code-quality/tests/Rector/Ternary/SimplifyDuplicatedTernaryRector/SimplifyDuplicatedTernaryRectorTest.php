<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\Ternary\SimplifyDuplicatedTernaryRector;

use Iterator;
use Rector\CodeQuality\Rector\Ternary\SimplifyDuplicatedTernaryRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class SimplifyDuplicatedTernaryRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return SimplifyDuplicatedTernaryRector::class;
    }
}
