<?php

declare(strict_types=1);

namespace Rector\Carbon\Tests\Rector\MethodCall\ChangeDiffForHumansArgsRector;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ChangeDiffForHumansArgsRectorTest extends AbstractRectorTestCase
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
        return \Rector\Carbon\Rector\MethodCall\ChangeDiffForHumansArgsRector::class;
    }
}
