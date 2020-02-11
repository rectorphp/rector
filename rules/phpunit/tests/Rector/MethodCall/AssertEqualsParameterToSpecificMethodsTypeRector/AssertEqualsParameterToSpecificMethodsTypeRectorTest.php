<?php

declare(strict_types=1);

namespace Rector\PHPUnit\Tests\Rector\MethodCall\AssertEqualsParameterToSpecificMethodsTypeRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\PHPUnit\Rector\MethodCall\AssertEqualsParameterToSpecificMethodsTypeRector;

final class AssertEqualsParameterToSpecificMethodsTypeRectorTest extends AbstractRectorTestCase
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
        return AssertEqualsParameterToSpecificMethodsTypeRector::class;
    }
}
