<?php

declare(strict_types=1);

namespace Rector\Defluent\Tests\Rector\Return_\ReturnNewFluentChainMethodCallToNonFluentRector;

use Iterator;
use Rector\Defluent\Rector\Return_\ReturnNewFluentChainMethodCallToNonFluentRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ReturnNewFluentChainMethodCallToNonFluentRectorTest extends AbstractRectorTestCase
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
        return ReturnNewFluentChainMethodCallToNonFluentRector::class;
    }
}
