<?php

declare(strict_types=1);

namespace Rector\Naming\Tests\Rector\ClassMethod\MakeIsserClassMethodNameStartWithIsRector;

use Iterator;
use Rector\Naming\Rector\ClassMethod\MakeIsserClassMethodNameStartWithIsRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class MakeIsserClassMethodNameStartWithIsRectorTest extends AbstractRectorTestCase
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
        return MakeIsserClassMethodNameStartWithIsRector::class;
    }
}
