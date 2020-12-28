<?php

declare(strict_types=1);

namespace Rector\TestPackageName\Tests\Rector\Arg\TestRector;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class TestRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(\Symplify\SmartFileSystem\SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    public function provideData(): \Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return \Rector\TestPackageName\Rector\Arg\TestRector::class;
    }
}
