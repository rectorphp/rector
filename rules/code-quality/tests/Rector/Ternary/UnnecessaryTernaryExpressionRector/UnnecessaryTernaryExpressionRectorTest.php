<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\Ternary\UnnecessaryTernaryExpressionRector;

use Iterator;
use Rector\CodeQuality\Rector\Ternary\UnnecessaryTernaryExpressionRector;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class UnnecessaryTernaryExpressionRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $file): void
    {
        $this->doTestFileInfo($file);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return UnnecessaryTernaryExpressionRector::class;
    }
}
