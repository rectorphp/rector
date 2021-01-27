<?php

declare(strict_types=1);

namespace Rector\Renaming\Tests\Rector\String_\RenameStringRector;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RenameStringRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(\Symplify\SmartFileSystem\SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    public function provideData(): \Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            \Rector\Renaming\Rector\String_\RenameStringRector::class =>
                [
                    \Rector\Renaming\Rector\String_\RenameStringRector::STRING_CHANGES => [
                        'ROLE_PREVIOUS_ADMIN' => 'IS_IMPERSONATOR',
                    ],
                ],
        ];
    }
}
