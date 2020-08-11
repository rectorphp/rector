<?php

declare(strict_types=1);

namespace Rector\Generic\Tests\Rector\StaticCall\RemoveParentCallByParentRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Generic\Rector\StaticCall\RemoveParentCallByParentRector;
use Rector\Generic\Tests\Rector\StaticCall\RemoveParentCallByParentRector\Source\ParentClassToRemoveParentStaticCallBy;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RemoveParentCallByParentRectorTest extends AbstractRectorTestCase
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
            RemoveParentCallByParentRector::class => [
                RemoveParentCallByParentRector::PARENT_CLASSES => [ParentClassToRemoveParentStaticCallBy::class],
            ],
        ];
    }
}
