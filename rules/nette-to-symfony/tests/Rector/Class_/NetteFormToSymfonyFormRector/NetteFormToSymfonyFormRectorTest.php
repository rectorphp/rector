<?php

declare(strict_types=1);

namespace Rector\NetteToSymfony\Tests\Rector\Class_\NetteFormToSymfonyFormRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\NetteToSymfony\Rector\Class_\NetteFormToSymfonyFormRector;

final class NetteFormToSymfonyFormRectorTest extends AbstractRectorTestCase
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
        return NetteFormToSymfonyFormRector::class;
    }
}
