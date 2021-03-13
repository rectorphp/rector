<?php

declare(strict_types=1);

namespace Rector\Tests\Naming\Rector\ClassMethod\MakeGetterClassMethodNameStartWithGetRector;

use Iterator;
use Rector\Naming\Rector\ClassMethod\MakeGetterClassMethodNameStartWithGetRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class MakeGetterClassMethodNameStartWithGetRectorTest extends AbstractRectorTestCase
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
        return MakeGetterClassMethodNameStartWithGetRector::class;
    }
}
