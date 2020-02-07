<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\FuncCall\CallUserFuncCallToVariadicRector;

use Iterator;
use Rector\CodingStyle\Rector\FuncCall\CallUserFuncCallToVariadicRector;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;

final class CallUserFuncCallToVariadicRectorTest extends AbstractRectorTestCase
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

    protected function getRectorClass(): string
    {
        return CallUserFuncCallToVariadicRector::class;
    }
}
