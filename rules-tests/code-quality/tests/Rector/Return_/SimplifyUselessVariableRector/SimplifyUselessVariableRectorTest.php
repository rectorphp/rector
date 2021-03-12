<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\Return_\SimplifyUselessVariableRector;

use Iterator;
use Rector\CodeQuality\Rector\Return_\SimplifyUselessVariableRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * Tests copied from:
 * - https://github.com/slevomat/coding-standard/blob/9978172758e90bc2355573e0b5d99062d87b14a3/tests/Sniffs/Variables/data/uselessVariableErrors.fixed.php
 * - https://github.com/slevomat/coding-standard/blob/9978172758e90bc2355573e0b5d99062d87b14a3/tests/Sniffs/Variables/data/uselessVariableNoErrors.php
 */
final class SimplifyUselessVariableRectorTest extends AbstractRectorTestCase
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
        return SimplifyUselessVariableRector::class;
    }
}
