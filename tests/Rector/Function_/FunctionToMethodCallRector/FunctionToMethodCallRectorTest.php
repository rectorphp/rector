<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Rector\Function_\FunctionToMethodCallRector;

use Iterator;
use Rector\Core\Rector\Function_\FunctionToMethodCallRector;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;

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
