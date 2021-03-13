<?php

declare(strict_types=1);

namespace Rector\Tests\NetteToSymfony\Rector\MethodCall\NetteFormToSymfonyFormRector;

use Iterator;
use Rector\NetteToSymfony\Rector\MethodCall\NetteFormToSymfonyFormRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class NetteFormToSymfonyFormRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    /**
     * @return Iterator<mixed, SmartFileInfo>
     */
    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return NetteFormToSymfonyFormRector::class;
    }
}
