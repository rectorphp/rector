<?php

declare(strict_types=1);

namespace Rector\Silverstripe\Tests\Rector\ConstantToStaticCallRector;

use Iterator;
use Rector\Silverstripe\Rector\ConstantToStaticCallRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ConstantToStaticCallRectorTest extends AbstractRectorTestCase
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
        return ConstantToStaticCallRector::class;
    }
}
