<?php

declare(strict_types=1);

namespace Rector\Nette\Tests\Rector\NotIdentical\StrposToStringsContainsRector;

use Iterator;
use Rector\Nette\Rector\NotIdentical\StrposToStringsContainsRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class StrposToStringsContainsRectorTest extends AbstractRectorTestCase
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
        return StrposToStringsContainsRector::class;
    }
}
