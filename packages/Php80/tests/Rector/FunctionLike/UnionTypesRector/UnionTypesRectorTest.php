<?php

declare(strict_types=1);

namespace Rector\Php80\Tests\Rector\FunctionLike\UnionTypesRector;

use Iterator;
use Rector\Php80\Rector\FunctionLike\UnionTypesRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class UnionTypesRectorTest extends AbstractRectorTestCase
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
        return UnionTypesRector::class;
    }
}
