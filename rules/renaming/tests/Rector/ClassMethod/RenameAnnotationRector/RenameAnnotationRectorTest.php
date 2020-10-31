<?php

declare(strict_types=1);

namespace Rector\Renaming\Tests\Rector\ClassMethod\RenameAnnotationRector;

use Iterator;
use Rector\Renaming\Rector\ClassMethod\RenameAnnotationRector;
use Rector\Renaming\ValueObject\RenameAnnotation;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RenameAnnotationRectorTest extends AbstractRectorTestCase
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
     * @return array<string, mixed[]>
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            RenameAnnotationRector::class => [
                RenameAnnotationRector::RENAMED_ANNOTATIONS_IN_TYPES => [
                    new RenameAnnotation('PHPUnit\Framework\TestCase', 'scenario', 'test'),
                ],
            ],
        ];
    }
}
