<?php

declare(strict_types=1);

namespace Rector\Symfony\Tests\Rector\Process\ProcessBuilderGetProcessRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Symfony\Rector\Process\ProcessBuilderGetProcessRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ProcessBuilderGetProcessRectorTest extends AbstractRectorTestCase
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
        return ProcessBuilderGetProcessRector::class;
    }
}
