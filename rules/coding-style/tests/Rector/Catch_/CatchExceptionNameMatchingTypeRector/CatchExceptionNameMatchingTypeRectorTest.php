<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\Catch_\CatchExceptionNameMatchingTypeRector;

use Iterator;
use Rector\CodingStyle\Rector\Catch_\CatchExceptionNameMatchingTypeRector;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class CatchExceptionNameMatchingTypeRectorTest extends AbstractRectorTestCase
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
        return CatchExceptionNameMatchingTypeRector::class;
    }
}
