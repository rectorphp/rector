<?php

declare(strict_types=1);

namespace Rector\Order\Tests\Rector\Class_\OrderPublicInterfaceMethodRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Order\Rector\Class_\OrderPublicInterfaceMethodRector;
use Rector\Order\Tests\Rector\Class_\OrderPublicInterfaceMethodRector\Source\FoodRecipeInterface;
use Symplify\SmartFileSystem\SmartFileInfo;

final class OrderPublicInterfaceMethodRectorTest extends AbstractRectorTestCase
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
            OrderPublicInterfaceMethodRector::class => [
                '$methodOrderByInterfaces' => [
                    FoodRecipeInterface::class => ['getDescription', 'process'],
                ],
            ],
        ];
    }
}
