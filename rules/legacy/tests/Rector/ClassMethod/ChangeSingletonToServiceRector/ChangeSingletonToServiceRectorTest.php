<?php

declare(strict_types=1);

namespace Rector\Legacy\Tests\Rector\ClassMethod\ChangeSingletonToServiceRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Legacy\Rector\ClassMethod\ChangeSingletonToServiceRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ChangeSingletonToServiceRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $file): void
    {
        $this->doTestFileInfo($file);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return ChangeSingletonToServiceRector::class;
    }
}
