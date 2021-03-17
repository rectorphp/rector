<?php

declare(strict_types=1);

namespace Rector\Tests\NetteCodeQuality\Rector\Class_\MoveInjectToExistingConstructorRector;

use Iterator;
use Rector\NetteCodeQuality\Rector\Class_\MoveInjectToExistingConstructorRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class MoveInjectToExistingConstructorRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    /**
     * @return Iterator<SmartFileInfo>
     */
    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return MoveInjectToExistingConstructorRector::class;
    }
}
