<?php

declare(strict_types=1);

namespace Rector\Generic\Tests\Rector\ClassMethod\AddMethodParentCallRector;

use Iterator;
use Rector\Generic\Rector\ClassMethod\AddMethodParentCallRector;
use Rector\Generic\Tests\Rector\ClassMethod\AddMethodParentCallRector\Source\ParentClassWithNewConstructor;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class AddMethodParentCallRectorTest extends AbstractRectorTestCase
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
            AddMethodParentCallRector::class => [
                AddMethodParentCallRector::METHODS_BY_PARENT_TYPES => [
                    ParentClassWithNewConstructor::class => '__construct',
                ],
            ],
        ];
    }
}
