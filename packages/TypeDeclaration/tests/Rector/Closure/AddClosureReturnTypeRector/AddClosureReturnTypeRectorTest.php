<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Tests\Rector\Closure\AddClosureReturnTypeRector;

use Iterator;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\TypeDeclaration\Rector\Closure\AddClosureReturnTypeRector;

final class AddClosureReturnTypeRectorTest extends AbstractRectorTestCase
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
        return AddClosureReturnTypeRector::class;
    }

    protected function getPhpVersion(): string
    {
        // prevent union types
        return '7.4';
    }
}
