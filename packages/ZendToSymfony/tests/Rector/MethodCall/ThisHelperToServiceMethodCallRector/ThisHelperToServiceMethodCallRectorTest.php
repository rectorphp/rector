<?php

declare(strict_types=1);

namespace Rector\ZendToSymfony\Tests\Rector\MethodCall\ThisHelperToServiceMethodCallRector;

use Iterator;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\ZendToSymfony\Rector\MethodCall\ThisHelperToServiceMethodCallRector;

final class ThisHelperToServiceMethodCallRectorTest extends AbstractRectorTestCase
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
        return ThisHelperToServiceMethodCallRector::class;
    }
}
