<?php

declare(strict_types=1);

namespace Rector\Generic\Tests\Rector\ClassMethod\WrapReturnRector;

use Iterator;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class WrapReturnRectorTest extends AbstractRectorTestCase
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

    protected function provideConfigFilePath(): string
    {
        return __DIR__ . '/config/configured_rule.php';
    }
}
