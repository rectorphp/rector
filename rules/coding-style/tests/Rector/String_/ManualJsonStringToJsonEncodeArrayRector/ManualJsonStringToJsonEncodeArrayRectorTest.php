<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\String_\ManualJsonStringToJsonEncodeArrayRector;

use Iterator;
use Rector\CodingStyle\Rector\String_\ManualJsonStringToJsonEncodeArrayRector;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ManualJsonStringToJsonEncodeArrayRectorTest extends AbstractRectorTestCase
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
        return ManualJsonStringToJsonEncodeArrayRector::class;
    }
}
