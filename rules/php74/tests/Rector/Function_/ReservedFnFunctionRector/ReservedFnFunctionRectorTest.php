<?php

declare(strict_types=1);

namespace Rector\Php74\Tests\Rector\Function_\ReservedFnFunctionRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Php74\Rector\Function_\ReservedFnFunctionRector;

final class ReservedFnFunctionRectorTest extends AbstractRectorTestCase
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

    protected function getRectorsWithConfiguration(): array
    {
        return [
            ReservedFnFunctionRector::class => [
                '$reservedNamesToNewOnes' => [
                    // for testing purposes of "fn" even on PHP 7.3-
                    'reservedFn' => 'f',
                ],
            ],
        ];
    }
}
