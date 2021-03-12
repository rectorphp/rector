<?php

declare(strict_types=1);

namespace Rector\Carbon\Tests\Rector\MethodCall\ChangeDiffForHumansArgsRector;

use Iterator;
use Rector\Carbon\Rector\MethodCall\ChangeDiffForHumansArgsRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ChangeDiffForHumansArgsRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return ChangeDiffForHumansArgsRector::class;
    }
}
