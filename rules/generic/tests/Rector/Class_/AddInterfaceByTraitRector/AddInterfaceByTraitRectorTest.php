<?php

declare(strict_types=1);

namespace Rector\Generic\Tests\Rector\Class_\AddInterfaceByTraitRector;

use Iterator;
use Rector\Generic\Rector\Class_\AddInterfaceByTraitRector;
use Rector\Generic\Tests\Rector\Class_\AddInterfaceByTraitRector\Source\SomeInterface;
use Rector\Generic\Tests\Rector\Class_\AddInterfaceByTraitRector\Source\SomeTrait;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class AddInterfaceByTraitRectorTest extends AbstractRectorTestCase
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
            AddInterfaceByTraitRector::class => [
                AddInterfaceByTraitRector::INTERFACE_BY_TRAIT => [
                    SomeTrait::class => SomeInterface::class,
                ],
            ],
        ];
    }
}
