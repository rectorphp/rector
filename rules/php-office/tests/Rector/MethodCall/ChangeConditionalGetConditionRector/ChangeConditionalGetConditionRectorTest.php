<?php

declare(strict_types=1);

namespace Rector\PHPOffice\Tests\Rector\MethodCall\ChangeConditionalGetConditionRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\PHPOffice\Rector\MethodCall\ChangeConditionalGetConditionRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ChangeConditionalGetConditionRectorTest extends AbstractRectorTestCase
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
        return ChangeConditionalGetConditionRector::class;
    }
}
