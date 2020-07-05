<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\Property\UnderscoreToPascalCasePropertyNameRector;

use Iterator;
use Rector\CodingStyle\Rector\Property\UnderscoreToPascalCasePropertyNameRector;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class UnderscoreToPascalCasePropertyNameRectorTest extends AbstractRectorTestCase
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
        return UnderscoreToPascalCasePropertyNameRector::class;
    }
}
