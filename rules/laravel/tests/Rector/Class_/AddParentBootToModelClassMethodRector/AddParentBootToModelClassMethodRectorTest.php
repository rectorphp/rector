<?php

declare(strict_types=1);

namespace Rector\Laravel\Tests\Rector\Class_\AddParentBootToModelClassMethodRector;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class AddParentBootToModelClassMethodRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(\Symplify\SmartFileSystem\SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    public function provideData(): \Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return \Rector\Laravel\Rector\Class_\AddParentBootToModelClassMethodRector::class;
    }
}
