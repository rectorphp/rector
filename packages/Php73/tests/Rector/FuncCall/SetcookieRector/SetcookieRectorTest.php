<?php

declare(strict_types=1);

namespace Rector\Php73\Tests\Rector\FuncCall\SetcookieRector;

use Iterator;
use Rector\Php73\Rector\FuncCall\SetcookieRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class SetcookieRectorTest extends AbstractRectorTestCase
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
        return SetcookieRector::class;
    }
}
