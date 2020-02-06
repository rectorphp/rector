<?php

declare(strict_types=1);

namespace Rector\Php72\Tests\Rector\FuncCall\CreateFunctionToAnonymousFunctionRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Php72\Rector\FuncCall\CreateFunctionToAnonymousFunctionRector;

final class CreateFunctionToAnonymousFunctionRectorTest extends AbstractRectorTestCase
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
        return CreateFunctionToAnonymousFunctionRector::class;
    }
}
