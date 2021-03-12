<?php

declare(strict_types=1);

namespace Rector\PHPUnit\Tests\Rector\MethodCall\ReplaceAssertArraySubsetWithDmsPolyfillRector;

use Iterator;
use Rector\PHPUnit\Rector\MethodCall\ReplaceAssertArraySubsetWithDmsPolyfillRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ReplaceAssertArraySubsetWithDmsPolyfillRectorTest extends AbstractRectorTestCase
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
        return ReplaceAssertArraySubsetWithDmsPolyfillRector::class;
    }
}
