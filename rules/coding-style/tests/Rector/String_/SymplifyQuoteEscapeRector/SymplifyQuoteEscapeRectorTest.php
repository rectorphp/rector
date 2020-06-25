<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\String_\SymplifyQuoteEscapeRector;

use Iterator;
use Rector\CodingStyle\Rector\String_\SymplifyQuoteEscapeRector;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class SymplifyQuoteEscapeRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $file): void
    {
        $this->doTestFileInfo($file);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return SymplifyQuoteEscapeRector::class;
    }
}
