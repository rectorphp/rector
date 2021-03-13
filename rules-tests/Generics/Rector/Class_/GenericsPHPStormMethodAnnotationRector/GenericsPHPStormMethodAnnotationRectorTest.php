<?php

declare(strict_types=1);

namespace Rector\Tests\Generics\Rector\Class_\GenericsPHPStormMethodAnnotationRector;

use Iterator;
use Rector\Generics\Rector\Class_\GenericsPHPStormMethodAnnotationRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class GenericsPHPStormMethodAnnotationRectorTest extends AbstractRectorTestCase
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
        return GenericsPHPStormMethodAnnotationRector::class;
    }
}
