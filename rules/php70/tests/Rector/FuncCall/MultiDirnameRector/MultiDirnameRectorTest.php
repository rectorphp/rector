<?php

declare(strict_types=1);

namespace Rector\Php70\Tests\Rector\FuncCall\MultiDirnameRector;

use Iterator;
use Rector\Php70\Rector\FuncCall\MultiDirnameRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * Some tests copied from:
 * - https://github.com/FriendsOfPHP/PHP-CS-Fixer/pull/3826/files
 */
final class MultiDirnameRectorTest extends AbstractRectorTestCase
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
        return MultiDirnameRector::class;
    }
}
