<?php

declare(strict_types=1);

namespace Rector\Carbon\Tests\Rector\MethodCall\ChangeCarbonSingularMethodCallToPluralRector;

use Iterator;
use Rector\Carbon\Rector\MethodCall\ChangeCarbonSingularMethodCallToPluralRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ChangeCarbonSingularMethodCallToPluralRectorTest extends AbstractRectorTestCase
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
        return ChangeCarbonSingularMethodCallToPluralRector::class;
    }
}
