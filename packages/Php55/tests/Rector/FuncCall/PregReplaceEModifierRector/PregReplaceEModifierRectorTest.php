<?php

declare(strict_types=1);

namespace Rector\Php55\Tests\Rector\FuncCall\PregReplaceEModifierRector;

use Iterator;
use Rector\Php55\Rector\FuncCall\PregReplaceEModifierRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class PregReplaceEModifierRectorTest extends AbstractRectorTestCase
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
        return PregReplaceEModifierRector::class;
    }
}
