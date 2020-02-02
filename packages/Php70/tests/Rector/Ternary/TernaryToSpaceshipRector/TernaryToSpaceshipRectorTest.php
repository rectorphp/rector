<?php

declare(strict_types=1);

namespace Rector\Php70\Tests\Rector\Ternary\TernaryToSpaceshipRector;

use Iterator;
use Rector\Php70\Rector\Ternary\TernaryToSpaceshipRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class TernaryToSpaceshipRectorTest extends AbstractRectorTestCase
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
        return TernaryToSpaceshipRector::class;
    }
}
