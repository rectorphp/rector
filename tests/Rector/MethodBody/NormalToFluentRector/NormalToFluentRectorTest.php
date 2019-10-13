<?php

declare(strict_types=1);

namespace Rector\Tests\Rector\MethodBody\NormalToFluentRector;

use Iterator;
use Rector\Rector\MethodBody\NormalToFluentRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Tests\Rector\MethodBody\NormalToFluentRector\Source\FluentInterfaceClass;

final class NormalToFluentRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/fixture2.php.inc'];
        yield [__DIR__ . '/Fixture/fixture3.php.inc'];
        yield [__DIR__ . '/Fixture/fixture4.php.inc'];
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            NormalToFluentRector::class => [
                '$fluentMethodsByType' => [
                    FluentInterfaceClass::class => ['someFunction', 'otherFunction', 'joinThisAsWell'],
                ],
            ],
        ];
    }
}
