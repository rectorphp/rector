<?php

declare(strict_types=1);

namespace Rector\NetteCodeQuality\Tests\Rector\Identical\SubstrMinusToStringEndsWithRector;

use Iterator;
use Rector\NetteCodeQuality\Rector\Identical\SubstrMinusToStringEndsWithRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class SubstrMinusToStringEndsWithRectorTest extends AbstractRectorTestCase
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
        return SubstrMinusToStringEndsWithRector::class;
    }
}
