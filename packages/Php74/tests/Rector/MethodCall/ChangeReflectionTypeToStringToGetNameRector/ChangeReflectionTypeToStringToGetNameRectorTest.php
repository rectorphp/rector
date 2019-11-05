<?php

declare(strict_types=1);

namespace Rector\Php74\Tests\Rector\MethodCall\ChangeReflectionTypeToStringToGetNameRector;

use Iterator;
use Rector\Php74\Rector\MethodCall\ChangeReflectionTypeToStringToGetNameRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ChangeReflectionTypeToStringToGetNameRectorTest extends AbstractRectorTestCase
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
        return ChangeReflectionTypeToStringToGetNameRector::class;
    }
}
