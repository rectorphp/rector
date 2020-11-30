<?php

declare(strict_types=1);

namespace Rector\Php74\Tests\Rector\LNumber\AddLiteralSeparatorToNumberRector;

use Iterator;
use Rector\Php74\Rector\LNumber\AddLiteralSeparatorToNumberRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class AddLiteralSeparatorToNumberRectorTest extends AbstractRectorTestCase
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
            AddLiteralSeparatorToNumberRector::class => [
                AddLiteralSeparatorToNumberRector::LIMIT_VALUE => 1000000,
            ],
        ];
    }
}
