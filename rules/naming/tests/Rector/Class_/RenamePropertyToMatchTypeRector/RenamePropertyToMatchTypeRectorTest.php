<?php

declare(strict_types=1);

namespace Rector\Naming\Tests\Rector\Class_\RenamePropertyToMatchTypeRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Naming\Rector\Class_\RenamePropertyToMatchTypeRector;

final class RenamePropertyToMatchTypeRectorTest extends AbstractRectorTestCase
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
        return RenamePropertyToMatchTypeRector::class;
    }
}
