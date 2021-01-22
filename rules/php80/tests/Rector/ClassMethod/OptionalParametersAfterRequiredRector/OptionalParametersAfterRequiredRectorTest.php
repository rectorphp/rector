<?php

declare(strict_types=1);

namespace Rector\Php80\Tests\Rector\ClassMethod\OptionalParametersAfterRequiredRector;

use Iterator;
use Rector\Php80\Rector\ClassMethod\OptionalParametersAfterRequiredRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class OptionalParametersAfterRequiredRectorTest extends AbstractRectorTestCase
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
        return OptionalParametersAfterRequiredRector::class;
    }
}
