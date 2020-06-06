<?php

declare(strict_types=1);

namespace Rector\Sensio\Tests\Rector\ClassMethod\RemoveServiceFromSensioRouteRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Sensio\Rector\ClassMethod\RemoveServiceFromSensioRouteRector;

final class RemoveServiceFromSensioRouteRectorTest extends AbstractRectorTestCase
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
        return RemoveServiceFromSensioRouteRector::class;
    }
}
