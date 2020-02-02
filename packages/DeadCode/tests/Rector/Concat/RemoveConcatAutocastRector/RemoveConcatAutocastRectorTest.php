<?php

declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\Concat\RemoveConcatAutocastRector;

use Iterator;
use Rector\DeadCode\Rector\Concat\RemoveConcatAutocastRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RemoveConcatAutocastRectorTest extends AbstractRectorTestCase
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
        return RemoveConcatAutocastRector::class;
    }
}
