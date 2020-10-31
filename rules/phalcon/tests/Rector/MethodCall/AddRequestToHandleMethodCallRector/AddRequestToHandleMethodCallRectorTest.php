<?php

declare(strict_types=1);

namespace Rector\Phalcon\Tests\Rector\MethodCall\AddRequestToHandleMethodCallRector;

use Iterator;
use Rector\Phalcon\Rector\MethodCall\AddRequestToHandleMethodCallRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class AddRequestToHandleMethodCallRectorTest extends AbstractRectorTestCase
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
        return AddRequestToHandleMethodCallRector::class;
    }
}
