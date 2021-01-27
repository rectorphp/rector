<?php

declare(strict_types=1);

namespace Rector\Renaming\Tests\Rector\String_\RenameStringRector;

use Iterator;
use Rector\Renaming\Rector\String_\RenameStringRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RenameStringRectorTest extends AbstractRectorTestCase
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
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            RenameStringRector::class =>
                [
                    RenameStringRector::STRING_CHANGES => [
                        'ROLE_PREVIOUS_ADMIN' => 'IS_IMPERSONATOR',
                    ],
                ],
        ];
    }
}
