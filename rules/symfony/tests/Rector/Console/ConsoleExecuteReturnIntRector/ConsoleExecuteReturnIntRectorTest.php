<?php

declare(strict_types=1);

namespace Rector\Symfony\Tests\Rector\Console\ConsoleExecuteReturnIntRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Symfony\Rector\Console\ConsoleExecuteReturnIntRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ConsoleExecuteReturnIntRectorTest extends AbstractRectorTestCase
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
        return ConsoleExecuteReturnIntRector::class;
    }
}
