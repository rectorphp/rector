<?php

declare(strict_types=1);

namespace Rector\Transform\Tests\Rector\FuncCall\FuncCallToNewRector;

use Iterator;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Transform\Rector\FuncCall\FuncCallToNewRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class FuncCallToNewRectorTest extends AbstractRectorTestCase
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

    /**
     * @return array<string, mixed[]>
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            FuncCallToNewRector::class => [
                FuncCallToNewRector::FUNCTIONS_TO_NEWS => [
                    'collection' => ['Collection'],
                ],
            ],
        ];
    }
}
