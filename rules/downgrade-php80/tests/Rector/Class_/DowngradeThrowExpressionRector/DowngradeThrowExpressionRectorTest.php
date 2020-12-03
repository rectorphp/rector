<?php

declare(strict_types=1);

namespace Rector\DowngradePhp80\Tests\Rector\Class_\DowngradeThrowExpressionRector;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class DowngradeThrowExpressionRectorTest extends AbstractRectorTestCase
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

    protected function getRectorClass(): string
    {
        return \Rector\DowngradePhp80\Rector\Class_\DowngradeThrowExpressionRector::class;
    }
}
