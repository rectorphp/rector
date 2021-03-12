<?php

declare(strict_types=1);

namespace Rector\Php70\Tests\Rector\MethodCall\ThisCallOnStaticMethodToStaticCallRector;

use Iterator;
use Rector\Php70\Rector\MethodCall\ThisCallOnStaticMethodToStaticCallRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ThisCallOnStaticMethodToStaticCallRectorTest extends AbstractRectorTestCase
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
        return ThisCallOnStaticMethodToStaticCallRector::class;
    }
}
