<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\Use_\RemoveUnusedAliasRector;

use Iterator;
use Rector\CodingStyle\Rector\Use_\RemoveUnusedAliasRector;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;

final class RemoveUnusedAliasRectorTest extends AbstractRectorTestCase
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
        return RemoveUnusedAliasRector::class;
    }
}
