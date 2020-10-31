<?php

declare(strict_types=1);

namespace Rector\Generic\Tests\Rector\Class_\MergeInterfacesRector;

use Iterator;
use Rector\Generic\Rector\Class_\MergeInterfacesRector;
use Rector\Generic\Tests\Rector\Class_\MergeInterfacesRector\Source\SomeInterface;
use Rector\Generic\Tests\Rector\Class_\MergeInterfacesRector\Source\SomeOldInterface;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class MergeInterfacesRectorTest extends AbstractRectorTestCase
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
            MergeInterfacesRector::class => [
                MergeInterfacesRector::OLD_TO_NEW_INTERFACES => [
                    SomeOldInterface::class => SomeInterface::class,
                ],
            ],
        ];
    }
}
