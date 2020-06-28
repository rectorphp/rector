<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Rector\FuncCall\RemoveFuncCallArgRector;

use Iterator;
use Rector\Core\Rector\FuncCall\RemoveFuncCallArgRector;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
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

    protected function getRectorsWithConfiguration(): array
    {
        return [
            RemoveFuncCallArgRector::class => [
                '$argumentPositionByFunctionName' => [
                    'ldap_first_attribute' => [2],
                ],
            ],
        ];
    }
}
