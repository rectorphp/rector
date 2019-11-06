<?php

declare(strict_types=1);

namespace Rector\Php74\Tests\Rector\FuncCall\GetCalledClassToStaticClassRector;

use Iterator;
use Rector\Php74\Rector\FuncCall\GetCalledClassToStaticClassRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class GetCalledClassToStaticClassRectorTest extends AbstractRectorTestCase
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
        return GetCalledClassToStaticClassRector::class;
    }
}
