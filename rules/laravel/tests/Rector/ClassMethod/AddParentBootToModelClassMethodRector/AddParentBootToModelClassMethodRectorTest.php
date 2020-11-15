<?php

declare(strict_types=1);

namespace Rector\Laravel\Tests\Rector\ClassMethod\AddParentBootToModelClassMethodRector;

use Iterator;
use Rector\Laravel\Rector\ClassMethod\AddParentBootToModelClassMethodRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class AddParentBootToModelClassMethodRectorTest extends AbstractRectorTestCase
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
        return AddParentBootToModelClassMethodRector::class;
    }
}
