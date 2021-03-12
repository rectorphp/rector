<?php

declare(strict_types=1);

namespace Rector\Symfony\Tests\Rector\Class_\MakeCommandLazyRector;

use Iterator;
use Rector\Symfony\Rector\Class_\MakeCommandLazyRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class MakeCommandLazyRectorTest extends AbstractRectorTestCase
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
        return MakeCommandLazyRector::class;
    }
}
