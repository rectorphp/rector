<?php

declare(strict_types=1);

namespace Rector\PHPOffice\Tests\Rector\MethodCall\ChangeConditionalSetConditionRector;

use Iterator;
use Rector\PHPOffice\Rector\MethodCall\ChangeConditionalSetConditionRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ChangeConditionalSetConditionRectorTest extends AbstractRectorTestCase
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
        return ChangeConditionalSetConditionRector::class;
    }
}
