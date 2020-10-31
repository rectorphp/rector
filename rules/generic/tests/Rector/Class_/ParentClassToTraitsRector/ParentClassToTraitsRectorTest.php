<?php

declare(strict_types=1);

namespace Rector\Generic\Tests\Rector\Class_\ParentClassToTraitsRector;

use Iterator;
use Rector\Generic\Rector\Class_\ParentClassToTraitsRector;
use Rector\Generic\Tests\Rector\Class_\ParentClassToTraitsRector\Source\AnotherParentObject;
use Rector\Generic\Tests\Rector\Class_\ParentClassToTraitsRector\Source\ParentObject;
use Rector\Generic\Tests\Rector\Class_\ParentClassToTraitsRector\Source\SecondTrait;
use Rector\Generic\Tests\Rector\Class_\ParentClassToTraitsRector\Source\SomeTrait;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ParentClassToTraitsRectorTest extends AbstractRectorTestCase
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
            ParentClassToTraitsRector::class => [
                ParentClassToTraitsRector::PARENT_CLASS_TO_TRAITS => [
                    ParentObject::class => [SomeTrait::class],
                    AnotherParentObject::class => [SomeTrait::class, SecondTrait::class],
                ],
            ],
        ];
    }
}
