<?php

declare(strict_types=1);

namespace Rector\Performance\Tests\Rector\FuncCall\CountArrayToEmptyArrayComparisonRule;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Performance\Rector\FuncCall\CountArrayToEmptyArrayComparisonRule;
use Symplify\SmartFileSystem\SmartFileInfo;

final class CountArrayToEmptyArrayComparisonRuleTest extends AbstractRectorTestCase
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
        return CountArrayToEmptyArrayComparisonRule::class;
    }
}
