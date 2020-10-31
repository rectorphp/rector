<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector;

use Iterator;
use Rector\CodingStyle\Rector\Throw_\AnnotateThrowablesRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class AnnotateThrowablesRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    public function provideDataForTest(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return AnnotateThrowablesRector::class;
    }
}
