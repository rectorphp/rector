<?php

declare(strict_types=1);

namespace Rector\Generic\Tests\Rector\Class_\RemoveInterfacesRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Generic\Rector\Class_\RemoveInterfacesRector;
use Rector\Generic\Tests\Rector\Class_\RemoveInterfacesRector\Source\SomeInterface;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RemoveInterfacesRectorTest extends AbstractRectorTestCase
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
            RemoveInterfacesRector::class => [
                RemoveInterfacesRector::INTERFACES_TO_REMOVE => [SomeInterface::class],
            ],
        ];
    }
}
