<?php

declare(strict_types=1);

namespace Rector\Php70\Tests\Rector\FuncCall\RenameMktimeWithoutArgsToTimeRector;

use Iterator;
use Rector\Php70\Rector\FuncCall\RenameMktimeWithoutArgsToTimeRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RenameMktimeWithoutArgsToTimeRectorTest extends AbstractRectorTestCase
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
        return RenameMktimeWithoutArgsToTimeRector::class;
    }
}
