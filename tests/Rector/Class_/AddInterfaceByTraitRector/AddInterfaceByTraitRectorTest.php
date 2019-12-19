<?php

declare(strict_types=1);

namespace Rector\Tests\Rector\Class_\AddInterfaceByTraitRector;

use Iterator;
use Rector\Rector\Class_\AddInterfaceByTraitRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Tests\Rector\Class_\AddInterfaceByTraitRector\Source\SomeInterface;
use Rector\Tests\Rector\Class_\AddInterfaceByTraitRector\Source\SomeTrait;

final class AddInterfaceByTraitRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    public function provideDataForTest(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorsWithConfiguration(): array
    {
        return [
            AddInterfaceByTraitRector::class => [
                '$interfaceByTrait' => [
                    SomeTrait::class => SomeInterface::class
                ]
            ]
        ];
    }
}
