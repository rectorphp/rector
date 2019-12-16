<?php

declare(strict_types=1);

namespace Rector\Phalcon\Tests\Rector\Assign\NewApplicationToToFactoryWithDefaultContainerRector;

use Iterator;
use Rector\Phalcon\Rector\Assign\NewApplicationToToFactoryWithDefaultContainerRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class NewApplicationToToFactoryWithDefaultContainerRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    public function provideDataForTest(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return NewApplicationToToFactoryWithDefaultContainerRector::class;
    }
}
