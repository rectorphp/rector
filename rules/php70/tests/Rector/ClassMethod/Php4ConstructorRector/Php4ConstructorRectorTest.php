<?php

declare(strict_types=1);

namespace Rector\Php70\Tests\Rector\ClassMethod\Php4ConstructorRector;

use Iterator;
use Rector\Php70\Rector\ClassMethod\Php4ConstructorRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * Some test cases used from:
 * - https://github.com/FriendsOfPHP/PHP-CS-Fixer/blob/2.12/tests/Fixer/ClassNotation/NoPhp4ConstructorFixerTest.php
 */
final class Php4ConstructorRectorTest extends AbstractRectorTestCase
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
        return Php4ConstructorRector::class;
    }
}
