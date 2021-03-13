<?php

declare(strict_types=1);

namespace Rector\Tests\CodingStyle\Rector\Function_\CamelCaseFunctionNamingToUnderscoreRector;

use Iterator;
use Rector\CodingStyle\Rector\Function_\CamelCaseFunctionNamingToUnderscoreRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class CamelCaseFunctionNamingToUnderscoreRectorTest extends AbstractRectorTestCase
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
        return CamelCaseFunctionNamingToUnderscoreRector::class;
    }
}
