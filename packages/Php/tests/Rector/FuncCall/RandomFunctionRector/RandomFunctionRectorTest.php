<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\FuncCall\RandomFunctionRector;

use Rector\Php\Rector\FuncCall\RandomFunctionRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

/**
 * Some tests copied from https://github.com/FriendsOfPHP/PHP-CS-Fixer/blob/2.12/tests/Fixer/Alias/RandomApiMigrationFixerTest.php
 */
final class RandomFunctionRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Wrong/wrong.php.inc']);
    }

    public function getRectorClass(): string
    {
        return RandomFunctionRector::class;
    }
}
