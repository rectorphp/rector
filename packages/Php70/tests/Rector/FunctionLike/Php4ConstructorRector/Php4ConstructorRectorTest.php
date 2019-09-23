<?php declare(strict_types=1);

namespace Rector\Php70\Tests\Rector\FunctionLike\Php4ConstructorRector;

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

    /**
     * @return string[]
     */
    public function provideDataForTest(): iterable
    {
        yield [__DIR__ . '/Fixture/fixture.php.inc'];
        yield [__DIR__ . '/Fixture/in_namespace.php.inc'];
        yield [__DIR__ . '/Fixture/delegating.php.inc'];
        yield [__DIR__ . '/Fixture/delegating_2.php.inc'];
        yield [__DIR__ . '/Fixture/fixture5.php.inc'];
        yield [__DIR__ . '/Fixture/non_expression.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return Php4ConstructorRector::class;
    }
}
