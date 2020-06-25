<?php

declare(strict_types=1);

namespace Rector\PHPUnit\Tests\Rector\SpecificMethod\AssertInstanceOfComparisonRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\PHPUnit\Rector\SpecificMethod\AssertInstanceOfComparisonRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class AssertInstanceOfComparisonRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $file): void
    {
        $this->doTestFileInfo($file);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return AssertInstanceOfComparisonRector::class;
    }
}
