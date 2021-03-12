<?php

declare(strict_types=1);

namespace Rector\Php52\Tests\Rector\Property\VarToPublicPropertyRector;

use Iterator;
use Rector\Php52\Rector\Property\VarToPublicPropertyRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class VarToPublicPropertyRectorTest extends AbstractRectorTestCase
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
        return VarToPublicPropertyRector::class;
    }
}
