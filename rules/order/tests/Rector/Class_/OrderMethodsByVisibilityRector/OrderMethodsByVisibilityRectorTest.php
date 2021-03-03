<?php

declare(strict_types=1);

namespace Rector\Order\Tests\Rector\Class_\OrderMethodsByVisibilityRector;

use Iterator;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class OrderMethodsByVisibilityRectorTest extends AbstractRectorTestCase
{
    /**
     * Final + private method breaks :)
     * @requires PHP < 8.0
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

    public function provideConfigFilePath(): string
    {
        return __DIR__ . '/config/configured_rule.php';
    }
}
