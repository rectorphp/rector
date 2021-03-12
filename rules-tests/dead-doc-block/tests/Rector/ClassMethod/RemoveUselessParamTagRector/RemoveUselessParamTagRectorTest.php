<?php

declare(strict_types=1);

namespace Rector\DeadDocBlock\Tests\Rector\ClassMethod\RemoveUselessParamTagRector;

use Iterator;
use Rector\DeadDocBlock\Rector\ClassMethod\RemoveUselessParamTagRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use SplFileInfo;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RemoveUselessParamTagRectorTest extends AbstractRectorTestCase
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
        return RemoveUselessParamTagRector::class;
    }
}
