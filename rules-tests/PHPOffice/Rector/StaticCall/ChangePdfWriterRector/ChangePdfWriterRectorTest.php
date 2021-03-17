<?php

declare(strict_types=1);

namespace Rector\Tests\PHPOffice\Rector\StaticCall\ChangePdfWriterRector;

use Iterator;
use Rector\PHPOffice\Rector\StaticCall\ChangePdfWriterRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ChangePdfWriterRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    /**
     * @return Iterator<SmartFileInfo>
     */
    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return ChangePdfWriterRector::class;
    }
}
