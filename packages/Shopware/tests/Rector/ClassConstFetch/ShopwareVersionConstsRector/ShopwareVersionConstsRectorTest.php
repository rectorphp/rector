<?php

declare(strict_types=1);

namespace Rector\Shopware\Tests\Rector\ClassConstFetch\ShopwareVersionConstsRector;

use Iterator;
use Rector\Shopware\Rector\ClassConstFetch\ShopwareVersionConstsRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ShopwareVersionConstsRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/fixture.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return ShopwareVersionConstsRector::class;
    }
}
