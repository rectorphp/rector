<?php

declare(strict_types=1);

namespace Rector\Doctrine\Tests\Rector\Class_\AddEntityIdByConditionRector;

use Iterator;
use Rector\Doctrine\Rector\Class_\AddEntityIdByConditionRector;
use Rector\Doctrine\Tests\Rector\Class_\AddEntityIdByConditionRector\Source\SomeTrait;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class AddEntityIdByConditionRectorTest extends AbstractRectorTestCase
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
            AddEntityIdByConditionRector::class => [
                AddEntityIdByConditionRector::DETECTED_TRAITS => [SomeTrait::class],
            ],
        ];
    }
}
