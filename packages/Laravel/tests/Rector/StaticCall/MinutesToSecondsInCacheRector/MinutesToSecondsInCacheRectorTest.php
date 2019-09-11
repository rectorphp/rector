<?php declare(strict_types=1);

namespace Rector\Laravel\Tests\Rector\StaticCall\MinutesToSecondsInCacheRector;

use Rector\Laravel\Rector\StaticCall\MinutesToSecondsInCacheRector;
use Rector\Laravel\Tests\Rector\StaticCall\MinutesToSecondsInCacheRector\Source\LaravelStoreInterface;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class MinutesToSecondsInCacheRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/skip_call.php.inc'];
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            MinutesToSecondsInCacheRector::class => [
                '$storeClass' => LaravelStoreInterface::class, // just for test case
            ],
        ];
    }
}
