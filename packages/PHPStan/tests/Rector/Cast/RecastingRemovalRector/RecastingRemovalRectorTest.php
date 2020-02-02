<?php

declare(strict_types=1);

namespace Rector\PHPStan\Tests\Rector\Cast\RecastingRemovalRector;

use Iterator;
use Rector\PHPStan\Rector\Cast\RecastingRemovalRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RecastingRemovalRectorTest extends AbstractRectorTestCase
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
        return RecastingRemovalRector::class;
    }
}
