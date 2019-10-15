<?php

declare(strict_types=1);

namespace Rector\PHPUnit\Tests\Rector\Class_\RemoveDataProviderTestPrefixRector;

use Iterator;
use Rector\PHPUnit\Rector\Class_\RemoveDataProviderTestPrefixRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RemoveDataProviderTestPrefixRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/with_test_annotation.php.inc'];
        yield [__DIR__ . '/Fixture/multiple_data_providers.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return RemoveDataProviderTestPrefixRector::class;
    }
}
