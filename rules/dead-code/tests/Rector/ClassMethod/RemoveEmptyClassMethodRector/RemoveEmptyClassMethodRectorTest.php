<?php

declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveEmptyClassMethodRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\DeadCode\Rector\ClassMethod\RemoveEmptyClassMethodRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RemoveEmptyClassMethodRectorTest extends AbstractRectorTestCase
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
        return RemoveEmptyClassMethodRector::class;
    }
}
