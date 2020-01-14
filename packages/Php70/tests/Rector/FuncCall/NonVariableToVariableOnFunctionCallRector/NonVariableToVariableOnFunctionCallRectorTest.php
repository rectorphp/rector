<?php

declare(strict_types=1);

namespace Rector\Php70\Tests\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector;

use Iterator;
use Rector\Php70\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class NonVariableToVariableOnFunctionCallRectorTest extends AbstractRectorTestCase
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
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return NonVariableToVariableOnFunctionCallRector::class;
    }
}
