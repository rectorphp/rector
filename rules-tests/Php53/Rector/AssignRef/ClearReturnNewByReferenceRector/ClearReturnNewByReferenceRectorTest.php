<?php

declare(strict_types=1);

namespace Rector\Tests\Php53\Rector\AssignRef\ClearReturnNewByReferenceRector;

use Iterator;
use Rector\Php53\Rector\AssignRef\ClearReturnNewByReferenceRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use SplFileInfo;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ClearReturnNewByReferenceRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    /**
     * @return Iterator<SplFileInfo>
     */
    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return ClearReturnNewByReferenceRector::class;
    }
}
