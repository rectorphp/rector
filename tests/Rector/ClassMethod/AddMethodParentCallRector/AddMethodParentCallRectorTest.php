<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Rector\ClassMethod\AddMethodParentCallRector;

use Iterator;
use Rector\Core\Rector\ClassMethod\AddMethodParentCallRector;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Core\Tests\Rector\ClassMethod\AddMethodParentCallRector\Source\ParentClassWithNewConstructor;
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
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            AddMethodParentCallRector::class => [
                '$methodsByParentTypes' => [
                    ParentClassWithNewConstructor::class => ['__construct'],
                ],
            ],
        ];
    }
}
