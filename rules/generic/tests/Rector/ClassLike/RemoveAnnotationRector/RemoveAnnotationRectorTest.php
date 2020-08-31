<?php

declare(strict_types=1);

namespace Rector\Generic\Tests\Rector\ClassLike\RemoveAnnotationRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Generic\Rector\ClassLike\RemoveAnnotationRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RemoveAnnotationRectorTest extends AbstractRectorTestCase
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

    /**
     * @return array<string, array<int, string[]>>
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            RemoveAnnotationRector::class => [
                RemoveAnnotationRector::ANNOTATIONS_TO_REMOVE => ['method'],
            ],
        ];
    }
}
