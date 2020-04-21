<?php

declare(strict_types=1);

namespace Rector\Php74\Tests\Rector\LNumber\AddLiteralSeparatorToNumberRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Php74\Rector\LNumber\AddLiteralSeparatorToNumberRector;

final class AddLiteralSeparatorToNumberRectorTest extends AbstractRectorTestCase
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
        return AddLiteralSeparatorToNumberRector::class;
    }
}
