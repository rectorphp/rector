<?php

declare(strict_types=1);

namespace Rector\Php70\Tests\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector;

use Iterator;
use Rector\Php70\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class NonVariableToVariableOnFunctionCallRectorTest extends AbstractRectorTestCase
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
        return NonVariableToVariableOnFunctionCallRector::class;
    }
}
