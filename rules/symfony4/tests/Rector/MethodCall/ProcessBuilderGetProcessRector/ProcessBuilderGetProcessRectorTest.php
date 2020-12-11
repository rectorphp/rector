<?php

declare(strict_types=1);

namespace Rector\Symfony4\Tests\Rector\MethodCall\ProcessBuilderGetProcessRector;

use Iterator;
use Rector\Symfony4\Rector\MethodCall\ProcessBuilderGetProcessRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ProcessBuilderGetProcessRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return ProcessBuilderGetProcessRector::class;
    }
}
