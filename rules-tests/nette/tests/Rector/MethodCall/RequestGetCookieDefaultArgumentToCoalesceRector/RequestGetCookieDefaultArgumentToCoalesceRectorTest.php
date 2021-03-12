<?php

declare(strict_types=1);

namespace Rector\Nette\Tests\Rector\MethodCall\RequestGetCookieDefaultArgumentToCoalesceRector;

use Iterator;
use Rector\Nette\Rector\MethodCall\RequestGetCookieDefaultArgumentToCoalesceRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RequestGetCookieDefaultArgumentToCoalesceRectorTest extends AbstractRectorTestCase
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
        return RequestGetCookieDefaultArgumentToCoalesceRector::class;
    }
}
