<?php

declare(strict_types=1);

namespace Rector\Tests\Rector\FuncCall\FunctionToNewRector;

use Iterator;
use Rector\Rector\FuncCall\FunctionToNewRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class FunctionToNewRectorTest extends AbstractRectorTestCase
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
