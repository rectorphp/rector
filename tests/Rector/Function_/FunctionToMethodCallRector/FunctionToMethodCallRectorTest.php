<?php

declare(strict_types=1);

namespace Rector\Tests\Rector\Function_\FunctionToMethodCallRector;

use Iterator;
use Rector\Rector\Function_\FunctionToMethodCallRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class FunctionToMethodCallRectorTest extends AbstractRectorTestCase
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
            FunctionToMethodCallRector::class => [
                '$functionToMethodCall' => [
                    'view' => ['this', 'render'],
                ],
            ],
        ];
    }
}
