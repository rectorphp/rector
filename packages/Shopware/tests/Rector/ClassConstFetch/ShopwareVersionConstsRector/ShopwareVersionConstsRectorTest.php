<?php

declare(strict_types=1);

namespace Rector\Shopware\Tests\Rector\ClassConstFetch\ShopwareVersionConstsRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Shopware\Rector\ClassConstFetch\ShopwareVersionConstsRector;

final class ShopwareVersionConstsRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return ShopwareVersionConstsRector::class;
    }
}
