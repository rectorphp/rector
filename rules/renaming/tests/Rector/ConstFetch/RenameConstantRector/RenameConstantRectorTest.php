<?php

declare(strict_types=1);

namespace Rector\Renaming\Tests\Rector\ConstFetch\RenameConstantRector;

use Iterator;
use Rector\Renaming\Rector\ConstFetch\RenameConstantRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RenameConstantRectorTest extends AbstractRectorTestCase
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
            RenameConstantRector::class => [
                RenameConstantRector::OLD_TO_NEW_CONSTANTS => [
                    'MYSQL_ASSOC' => 'MYSQLI_ASSOC',
                    'OLD_CONSTANT' => 'NEW_CONSTANT',
                ],
            ],
        ];
    }
}
