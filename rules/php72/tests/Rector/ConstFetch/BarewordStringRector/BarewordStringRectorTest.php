<?php

declare(strict_types=1);

namespace Rector\Php72\Tests\Rector\ConstFetch\BarewordStringRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Php72\Rector\ConstFetch\BarewordStringRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class BarewordStringRectorTest extends AbstractRectorTestCase
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
        return BarewordStringRector::class;
    }
}
