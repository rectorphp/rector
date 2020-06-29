<?php

declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\FunctionLike\RemoveCodeAfterReturnRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\DeadCode\Rector\FunctionLike\RemoveCodeAfterReturnRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RemoveCodeAfterReturnRectorTest extends AbstractRectorTestCase
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
        return RemoveCodeAfterReturnRector::class;
    }
}
