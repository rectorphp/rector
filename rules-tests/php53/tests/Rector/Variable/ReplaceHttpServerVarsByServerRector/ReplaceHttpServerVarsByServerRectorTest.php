<?php

declare(strict_types=1);

namespace Rector\Php53\Tests\Rector\Variable\ReplaceHttpServerVarsByServerRector;

use Iterator;
use Rector\Php53\Rector\Variable\ReplaceHttpServerVarsByServerRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use SplFileInfo;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ReplaceHttpServerVarsByServerRectorTest extends AbstractRectorTestCase
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
        return ReplaceHttpServerVarsByServerRector::class;
    }
}
