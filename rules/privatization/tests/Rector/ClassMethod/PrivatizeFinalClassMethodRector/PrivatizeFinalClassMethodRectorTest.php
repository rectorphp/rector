<?php

declare(strict_types=1);

namespace Rector\Privatization\Tests\Rector\ClassMethod\PrivatizeFinalClassMethodRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Privatization\Rector\ClassMethod\PrivatizeFinalClassMethodRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class PrivatizeFinalClassMethodRectorTest extends AbstractRectorTestCase
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
        return PrivatizeFinalClassMethodRector::class;
    }
}
