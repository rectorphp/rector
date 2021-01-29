<?php

declare(strict_types=1);

namespace Rector\Transform\Tests\Rector\Class_\ParentClassToTraitsRector;

use Iterator;
use Rector\Transform\Rector\Class_\ParentClassToTraitsRector;
use Rector\Transform\Tests\Rector\Class_\ParentClassToTraitsRector\Source\AnotherParentObject;
use Rector\Transform\Tests\Rector\Class_\ParentClassToTraitsRector\Source\ParentObject;
use Rector\Transform\Tests\Rector\Class_\ParentClassToTraitsRector\Source\SecondTrait;
use Rector\Transform\Tests\Rector\Class_\ParentClassToTraitsRector\Source\SomeTrait;
use Rector\Transform\ValueObject\ParentClassToTraits;
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
                    new ParentClassToTraits(ParentObject::class, [SomeTrait::class]),
                    new ParentClassToTraits(AnotherParentObject::class, [SomeTrait::class, SecondTrait::class]),
                ],
            ],
        ];
    }
}
