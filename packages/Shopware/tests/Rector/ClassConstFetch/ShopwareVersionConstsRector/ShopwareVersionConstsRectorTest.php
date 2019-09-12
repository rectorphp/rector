<?php declare(strict_types=1);

namespace Rector\Shopware\Tests\Rector\ClassConstFetch\ShopwareVersionConstsRector;

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

    /**
     * @return string[]
     */
    public function provideDataForTest(): iterable
    {
        yield [__DIR__ . '/Fixture/fixture.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return ShopwareVersionConstsRector::class;
    }
}
