<?php

declare(strict_types=1);

namespace Rector\CakePHPToSymfony\Tests\Rector\Class_\CakePHPBeforeFilterToRequestEventSubscriberRector;

use Iterator;
use Rector\CakePHPToSymfony\Rector\Class_\CakePHPBeforeFilterToRequestEventSubscriberRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class CakePHPBeforeFilterToRequestEventSubscriberRectorTest extends AbstractRectorTestCase
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
        return CakePHPBeforeFilterToRequestEventSubscriberRector::class;
    }
}
