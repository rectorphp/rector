<?php

declare(strict_types=1);

namespace Rector\Generic\Tests\Rector\Class_\AddPropertyByParentRector;

use Iterator;
use Rector\Generic\Rector\Class_\AddPropertyByParentRector;
use Rector\Generic\Tests\Rector\Class_\AddPropertyByParentRector\Source\SomeParentClassToAddDependencyBy;
use Rector\Generic\ValueObject\AddPropertyByParent;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class AddPropertyByParentRectorTest extends AbstractRectorTestCase
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
            AddPropertyByParentRector::class => [
                AddPropertyByParentRector::PARENT_DEPENDENCIES => [
                    new AddPropertyByParent(SomeParentClassToAddDependencyBy::class, 'SomeDependency'),
                ],
            ],
        ];
    }
}
