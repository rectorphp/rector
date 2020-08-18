<?php

declare(strict_types=1);

namespace Rector\Php53\Tests\Rector\AssignRef\ClearReturnNewByReferenceRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Php53\Rector\AssignRef\ClearReturnNewByReferenceRector;
use SplFileInfo;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ClearReturnNewByReferenceRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfoWithoutAutoload($fileInfo);
    }

    /**
     * @return Iterator<SplFileInfo>
     */
    public function provideDataForTest(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return ClearReturnNewByReferenceRector::class;
    }
}
