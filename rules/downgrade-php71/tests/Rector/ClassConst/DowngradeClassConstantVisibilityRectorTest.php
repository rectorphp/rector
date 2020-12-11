<?php

declare(strict_types=1);

namespace Rector\DowngradePhp71\Tests\Rector\ClassConst;

use Iterator;
use Rector\DowngradePhp71\Rector\ClassConst\DowngradeClassConstantVisibilityRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class DowngradeClassConstantVisibilityRectorTest extends AbstractRectorTestCase
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
        return DowngradeClassConstantVisibilityRector::class;
    }
}
