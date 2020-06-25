<?php

declare(strict_types=1);

namespace Rector\Symfony\Tests\Rector\Console\ConsoleExceptionToErrorEventConstantRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Symfony\Rector\Console\ConsoleExceptionToErrorEventConstantRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ConsoleExceptionToErrorEventConstantRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $file): void
    {
        $this->doTestFileInfo($file);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return ConsoleExceptionToErrorEventConstantRector::class;
    }
}
