<?php

declare(strict_types=1);

namespace Rector\Phalcon\Tests\Rector\MethodCall\AddRequestToHandleMethodCallRector;

use Iterator;
use Rector\Phalcon\Rector\MethodCall\AddRequestToHandleMethodCallRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class AddRequestToHandleMethodCallRectorTest extends AbstractRectorTestCase
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
        return AddRequestToHandleMethodCallRector::class;
    }
}
