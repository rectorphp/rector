<?php

declare(strict_types=1);

namespace Rector\Php70\Tests\Rector\FunctionLike\Php4ConstructorRector;

use Iterator;
use Rector\Php70\Rector\FunctionLike\Php4ConstructorRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

/**
 * Some test cases used from:
 * - https://github.com/FriendsOfPHP/PHP-CS-Fixer/blob/2.12/tests/Fixer/ClassNotation/NoPhp4ConstructorFixerTest.php
 */
final class Php4ConstructorRectorTest extends AbstractRectorTestCase
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
        return Php4ConstructorRector::class;
    }
}
