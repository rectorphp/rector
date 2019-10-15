<?php

declare(strict_types=1);

namespace Rector\Tests\Rector\FuncCall\FunctionToNewRector;

use Iterator;
use Rector\Rector\FuncCall\FunctionToNewRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class FunctionToNewRectorTest extends AbstractRectorTestCase
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
            FunctionToNewRector::class => [
                '$functionToNew' => [
                    'collection' => ['Collection'],
                ],
            ],
        ];
    }
}
