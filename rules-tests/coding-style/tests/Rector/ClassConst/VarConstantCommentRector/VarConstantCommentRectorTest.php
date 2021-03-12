<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\ClassConst\VarConstantCommentRector;

use Iterator;
use Rector\CodingStyle\Rector\ClassConst\VarConstantCommentRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class VarConstantCommentRectorTest extends AbstractRectorTestCase
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
        return VarConstantCommentRector::class;
    }
}
