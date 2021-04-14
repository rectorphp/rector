<?php

declare(strict_types=1);

namespace Rector\Tests\Downgrade73\Rector\FuncCall\DowngradeArrayKeyFirstLastRector;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class DowngradeArrayKeyFirstLastRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(\Symplify\SmartFileSystem\SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    public function provideData(): \Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    public function provideConfigFilePath(): string
    {
        return __DIR__ . '/config/configured_rule.php';
    }
}
