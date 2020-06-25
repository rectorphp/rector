<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Rector\Class_\AddInterfaceByTraitRector;

use Iterator;
use Rector\Core\Rector\Class_\AddInterfaceByTraitRector;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Core\Tests\Rector\Class_\AddInterfaceByTraitRector\Source\SomeInterface;
use Rector\Core\Tests\Rector\Class_\AddInterfaceByTraitRector\Source\SomeTrait;
use Symplify\SmartFileSystem\SmartFileInfo;

final class AddInterfaceByTraitRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $file): void
    {
        $this->doTestFileInfo($file);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorsWithConfiguration(): array
    {
        return [
            AddInterfaceByTraitRector::class => [
                '$interfaceByTrait' => [
                    SomeTrait::class => SomeInterface::class,
                ],
            ],
        ];
    }
}
