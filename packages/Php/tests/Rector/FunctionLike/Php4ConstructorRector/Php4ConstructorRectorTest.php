<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\FunctionLike\Php4ConstructorRector;

use Iterator;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

/**
 * @covers \Rector\Php\Rector\FunctionLike\Php4ConstructorRector
 *
 * Some test cases used from:
 * - https://github.com/FriendsOfPHP/PHP-CS-Fixer/blob/2.12/tests/Fixer/ClassNotation/NoPhp4ConstructorFixerTest.php
 */
final class Php4ConstructorRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideWrongToFixedFiles()
     */
    public function test(string $wrong, string $fixed): void
    {
        $this->doTestFileMatchesExpectedContent($wrong, $fixed);
    }

    public function provideWrongToFixedFiles(): Iterator
    {
        yield [__DIR__ . '/Wrong/wrong.php.inc', __DIR__ . '/Correct/correct.php.inc'];
        yield [__DIR__ . '/Wrong/InNamespacePhp4ConstructorClass.php', __DIR__ . '/Correct/correct2.php.inc'];
        yield [__DIR__ . '/Wrong/DelegatingPhp4ConstructorClass.php', __DIR__ . '/Correct/correct3.php.inc'];
        yield [__DIR__ . '/Wrong/DelegatingPhp4ConstructorClassAgain.php', __DIR__ . '/Correct/correct4.php.inc'];
        yield [__DIR__ . '/Wrong/wrong5.php.inc', __DIR__ . '/Correct/correct5.php.inc'];
    }

    protected function provideConfig(): string
    {
        return __DIR__ . '/config.yml';
    }
}
