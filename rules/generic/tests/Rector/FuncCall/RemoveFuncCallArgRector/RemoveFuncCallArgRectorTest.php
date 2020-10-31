<?php

declare(strict_types=1);

namespace Rector\Generic\Tests\Rector\FuncCall\RemoveFuncCallArgRector;

use Iterator;
use Rector\Generic\Rector\FuncCall\RemoveFuncCallArgRector;
use Rector\Generic\ValueObject\RemoveFuncCallArg;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use SplFileInfo;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RemoveFuncCallArgRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    /**
     * @return Iterator<SplFileInfo>
     */
    public function provideDataForTest(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    /**
     * @return array<string, mixed[]>
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            RemoveFuncCallArgRector::class => [
                RemoveFuncCallArgRector::REMOVED_FUNCTION_ARGUMENTS => [
                    new RemoveFuncCallArg('ldap_first_attribute', 2),
                ],
            ],
        ];
    }
}
