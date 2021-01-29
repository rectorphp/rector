<?php

declare(strict_types=1);

namespace Rector\Removing\Tests\Rector\FuncCall\RemoveFuncCallRector;

use Iterator;
use Rector\Removing\Rector\FuncCall\RemoveFuncCallRector;
use Rector\Removing\ValueObject\RemoveFuncCall;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use SplFileInfo;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RemoveFuncCallRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    /**
     * @return Iterator<SplFileInfo>
     */
    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    /**
     * @return array<string, mixed[]>
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            RemoveFuncCallRector::class => [
                RemoveFuncCallRector::REMOVE_FUNC_CALLS => [
                    new RemoveFuncCall('ini_get', [
                        0 => ['y2k_compliance', 'safe_mode', 'magic_quotes_runtime'],
                    ]),
                    new RemoveFuncCall('ini_set', [
                        0 => ['y2k_compliance', 'safe_mode', 'magic_quotes_runtime'],
                    ]),
                ],
            ],
        ];
    }
}
