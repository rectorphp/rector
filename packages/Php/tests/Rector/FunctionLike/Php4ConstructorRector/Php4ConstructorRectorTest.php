<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\FunctionLike\Php4ConstructorRector;

use Rector\Php\Rector\FunctionLike\Php4ConstructorRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

/**
 * Some test cases used from:
 * - https://github.com/FriendsOfPHP/PHP-CS-Fixer/blob/2.12/tests/Fixer/ClassNotation/NoPhp4ConstructorFixerTest.php
 */
final class Php4ConstructorRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Wrong/wrong.php.inc',
            __DIR__ . '/Wrong/in_namespace.php.inc',
            __DIR__ . '/Wrong/delegating.php.inc',
            __DIR__ . '/Wrong/delegating_2.php.inc',
            __DIR__ . '/Wrong/wrong5.php.inc',
        ]);
    }

    public function getRectorClass(): string
    {
        return Php4ConstructorRector::class;
    }
}
