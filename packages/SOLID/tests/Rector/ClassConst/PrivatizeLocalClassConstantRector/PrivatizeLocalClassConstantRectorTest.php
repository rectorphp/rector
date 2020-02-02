<?php

declare(strict_types=1);

namespace Rector\SOLID\Tests\Rector\ClassConst\PrivatizeLocalClassConstantRector;

use Iterator;
use Rector\SOLID\Rector\ClassConst\PrivatizeLocalClassConstantRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class PrivatizeLocalClassConstantRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    /**
     * @dataProvider provideDataProtected()
     */
    public function testProtected(string $file): void
    {
        $this->doTestFile($file);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    public function provideDataProtected(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return PrivatizeLocalClassConstantRector::class;
    }
}
