<?php

declare(strict_types=1);

namespace Rector\Symfony4\Tests\Rector\StaticCall\ProcessBuilderInstanceRector;

use Iterator;
use Rector\Symfony4\Rector\StaticCall\ProcessBuilderInstanceRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
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
