<?php

declare(strict_types=1);

namespace Rector\Php70\Tests\Rector\FuncCall\MultiDirnameRector;

use Iterator;
use Rector\Php70\Rector\FuncCall\MultiDirnameRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

/**
 * Some tests copied from:
 * - https://github.com/FriendsOfPHP/PHP-CS-Fixer/pull/3826/files
 */
final class MultiDirnameRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    public function provideDataForTest(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return MultiDirnameRector::class;
    }
}
