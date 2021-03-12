<?php

declare(strict_types=1);

namespace Rector\Tests\Symfony\Rector\ClassMethod\NormalizeAutowireMethodNamingRector;

use Iterator;
use Rector\Symfony\Rector\ClassMethod\NormalizeAutowireMethodNamingRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class NormalizeAutowireMethodNamingRectorTest extends AbstractRectorTestCase
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
        return NormalizeAutowireMethodNamingRector::class;
    }
}
