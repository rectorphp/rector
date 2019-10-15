<?php

declare(strict_types=1);

namespace Rector\Tests\Rector\Function_\FunctionToMethodCallRector;

use Iterator;
use Rector\Rector\Function_\FunctionToMethodCallRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class FunctionToMethodCallRectorTest extends AbstractRectorTestCase
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

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            FunctionToMethodCallRector::class => [
                '$functionToMethodCall' => [
                    'view' => ['this', 'render'],
                ],
            ],
        ];
    }
}
