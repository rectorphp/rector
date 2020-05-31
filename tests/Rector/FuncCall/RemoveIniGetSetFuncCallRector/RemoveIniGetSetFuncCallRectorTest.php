<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Rector\FuncCall\RemoveIniGetSetFuncCallRector;

use Iterator;
use Rector\Core\Rector\FuncCall\RemoveIniGetSetFuncCallRector;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use SplFileInfo;

final class RemoveIniGetSetFuncCallRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    /**
     * @return Iterator<SplFileInfo>
     */
    public function provideDataForTest(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorsWithConfiguration(): array
    {
        return [
            RemoveIniGetSetFuncCallRector::class => [
                '$keysToRemove' => ['y2k_compliance', 'safe_mode', 'magic_quotes_runtime'],
            ],
        ];
    }
}
