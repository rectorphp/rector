<?php

declare(strict_types=1);

namespace Rector\Php56\Tests\Rector\FunctionLike\AddDefaultValueForUndefinedVariableRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Php56\Rector\FunctionLike\AddDefaultValueForUndefinedVariableRector;

final class AddDefaultValueForUndefinedVariableRectorTest extends AbstractRectorTestCase
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
     * @dataProvider provideDataPhp74()
     * @requires PHP >= 7.4
     */
    public function testPhp74(string $file): void
    {
        $this->doTestFile($file);
    }

    public function provideDataPhp74(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/FixturePhp74');
    }

    protected function getRectorClass(): string
    {
        return AddDefaultValueForUndefinedVariableRector::class;
    }
}
