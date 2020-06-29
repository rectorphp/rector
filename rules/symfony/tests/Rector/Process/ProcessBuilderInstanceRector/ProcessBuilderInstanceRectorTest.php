<?php

declare(strict_types=1);

namespace Rector\Symfony\Tests\Rector\Process\ProcessBuilderInstanceRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Symfony\Rector\Process\ProcessBuilderInstanceRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ProcessBuilderInstanceRectorTest extends AbstractRectorTestCase
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
        return ProcessBuilderInstanceRector::class;
    }
}
