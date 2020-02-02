<?php

declare(strict_types=1);

namespace Rector\Symfony\Tests\Rector\New_\StringToArrayArgumentProcessRector;

use Iterator;
use Rector\Symfony\Rector\New_\StringToArrayArgumentProcessRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class StringToArrayArgumentProcessRectorTest extends AbstractRectorTestCase
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
        return StringToArrayArgumentProcessRector::class;
    }
}
