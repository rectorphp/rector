<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\FuncCall\CreateFunctionToAnonymousFunctionRector;

use Rector\Php\Rector\FuncCall\CreateFunctionToAnonymousFunctionRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class CreateFunctionToAnonymousFunctionRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/concat.php.inc'];
        yield [__DIR__ . '/Fixture/reference.php.inc'];
        yield [__DIR__ . '/Fixture/stackoverflow.php.inc'];
        yield [__DIR__ . '/Fixture/drupal.php.inc'];
        yield [__DIR__ . '/Fixture/php_net.php.inc'];
        yield [__DIR__ . '/Fixture/wordpress.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return CreateFunctionToAnonymousFunctionRector::class;
    }
}
