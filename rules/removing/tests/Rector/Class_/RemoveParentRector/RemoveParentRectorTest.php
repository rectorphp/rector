<?php

declare(strict_types=1);

namespace Rector\Removing\Tests\Rector\Class_\RemoveParentRector;

use Iterator;
use Rector\Removing\Rector\Class_\RemoveParentRector;
use Rector\Removing\Tests\Rector\Class_\RemoveParentRector\Source\ParentTypeToBeRemoved;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RemoveParentRectorTest extends AbstractRectorTestCase
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
            RemoveParentRector::class => [
                RemoveParentRector::PARENT_TYPES_TO_REMOVE => [ParentTypeToBeRemoved::class],
            ],
        ];
    }
}
