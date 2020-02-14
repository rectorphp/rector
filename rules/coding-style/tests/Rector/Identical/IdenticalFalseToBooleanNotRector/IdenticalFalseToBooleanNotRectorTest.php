<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\Identical\IdenticalFalseToBooleanNotRector;

use Iterator;
use Rector\CodingStyle\Rector\Identical\IdenticalFalseToBooleanNotRector;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;

final class IdenticalFalseToBooleanNotRectorTest extends AbstractRectorTestCase
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
        return IdenticalFalseToBooleanNotRector::class;
    }
}
