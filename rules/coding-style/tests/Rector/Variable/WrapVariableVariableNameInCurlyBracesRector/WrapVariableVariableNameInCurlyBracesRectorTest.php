<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\Variable\WrapVariableVariableNameInCurlyBracesRector;

use Iterator;
use Rector\CodingStyle\Rector\Variable\WrapVariableVariableNameInCurlyBracesRector;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class WrapVariableVariableNameInCurlyBracesRectorTest extends AbstractRectorTestCase
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
        return WrapVariableVariableNameInCurlyBracesRector::class;
    }
}
