<?php

declare(strict_types=1);

namespace Rector\Php74\Tests\Rector\Double\RealToFloatTypeCastRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Php74\Rector\Double\RealToFloatTypeCastRector;

final class RealToFloatTypeCastRectorTest extends AbstractRectorTestCase
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
        return RealToFloatTypeCastRector::class;
    }
}
