<?php

declare(strict_types=1);

namespace Rector\NetteKdyby\Tests\Rector\ClassMethod\KdybyEventSubscriberToContributteEventSubscriberRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\NetteKdyby\Rector\ClassMethod\KdybyEventSubscriberToContributteEventSubscriberRector;

final class KdybyEventSubscriberToContributteEventSubscriberRectorTest extends AbstractRectorTestCase
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
        return KdybyEventSubscriberToContributteEventSubscriberRector::class;
    }
}
