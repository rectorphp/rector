<?php

declare(strict_types=1);

namespace Rector\Order\Tests\Rector\Class_\OrderPublicInterfaceMethodRector;

use Iterator;
use Rector\Order\Rector\Class_\OrderPublicInterfaceMethodRector;
use Rector\Order\Tests\Rector\Class_\OrderPublicInterfaceMethodRector\Source\FoodRecipeInterface;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class OrderPublicInterfaceMethodRectorTest extends AbstractRectorTestCase
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
            OrderPublicInterfaceMethodRector::class => [
                OrderPublicInterfaceMethodRector::METHOD_ORDER_BY_INTERFACES => [
                    FoodRecipeInterface::class => ['getDescription', 'process'],
                ],
            ],
        ];
    }
}
