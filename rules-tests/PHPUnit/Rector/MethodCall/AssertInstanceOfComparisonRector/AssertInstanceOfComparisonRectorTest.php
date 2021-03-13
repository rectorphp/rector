<?php

declare(strict_types=1);

namespace Rector\Tests\PHPUnit\Rector\MethodCall\AssertInstanceOfComparisonRector;

use Iterator;
use Rector\PHPUnit\Rector\MethodCall\AssertInstanceOfComparisonRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class AssertInstanceOfComparisonRectorTest extends AbstractRectorTestCase
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
        return AssertInstanceOfComparisonRector::class;
    }
}
