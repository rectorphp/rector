<?php

declare(strict_types=1);

namespace Rector\Tests\Php54\Rector\FuncCall\RemoveReferenceFromCallRector;

use Iterator;
use Rector\Php54\Rector\FuncCall\RemoveReferenceFromCallRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RemoveReferenceFromCallRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    /**
     * @return Iterator<mixed, SmartFileInfo>
     */
    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return RemoveReferenceFromCallRector::class;
    }
}
