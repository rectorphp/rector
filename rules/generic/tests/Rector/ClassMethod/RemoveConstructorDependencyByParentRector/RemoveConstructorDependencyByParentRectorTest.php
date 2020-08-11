<?php

declare(strict_types=1);

namespace Rector\Generic\Tests\Rector\ClassMethod\RemoveConstructorDependencyByParentRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Generic\Rector\ClassMethod\RemoveConstructorDependencyByParentRector;
use Rector\Generic\Tests\Rector\ClassMethod\RemoveConstructorDependencyByParentRector\Source\ParentClassToRemoveConstructorParamsBy;
use Rector\Generic\Tests\Rector\ClassMethod\RemoveConstructorDependencyByParentRector\Source\SomeDependencyToBeRemoved;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RemoveConstructorDependencyByParentRectorTest extends AbstractRectorTestCase
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
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            RemoveConstructorDependencyByParentRector::class => [
                RemoveConstructorDependencyByParentRector::PARENT_TYPE_TO_PARAM_TYPES_TO_REMOVE => [
                    ParentClassToRemoveConstructorParamsBy::class => [SomeDependencyToBeRemoved::class],
                ],
            ],
        ];
    }
}
