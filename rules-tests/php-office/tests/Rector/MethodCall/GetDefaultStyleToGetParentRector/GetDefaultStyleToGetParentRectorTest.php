<?php

declare(strict_types=1);

namespace Rector\PHPOffice\Tests\Rector\MethodCall\GetDefaultStyleToGetParentRector;

use Iterator;
use Rector\PHPOffice\Rector\MethodCall\GetDefaultStyleToGetParentRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class GetDefaultStyleToGetParentRectorTest extends AbstractRectorTestCase
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
        return GetDefaultStyleToGetParentRector::class;
    }
}
