<?php

declare(strict_types=1);

namespace Rector\Php70\Tests\Rector\List_\EmptyListRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Php70\Rector\List_\EmptyListRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class EmptyListRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfoWithoutAutoload($fileInfo);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return EmptyListRector::class;
    }
}
