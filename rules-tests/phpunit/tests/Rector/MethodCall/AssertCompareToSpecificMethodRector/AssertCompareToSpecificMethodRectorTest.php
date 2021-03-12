<?php

declare(strict_types=1);

namespace Rector\PHPUnit\Tests\Rector\MethodCall\AssertCompareToSpecificMethodRector;

use Iterator;
use Rector\PHPUnit\Rector\MethodCall\AssertCompareToSpecificMethodRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class AssertCompareToSpecificMethodRectorTest extends AbstractRectorTestCase
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
        return AssertCompareToSpecificMethodRector::class;
    }
}
