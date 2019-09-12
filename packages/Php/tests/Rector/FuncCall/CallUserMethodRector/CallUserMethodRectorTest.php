<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\FuncCall\CallUserMethodRector;

use Rector\Php\Rector\FuncCall\CallUserMethodRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

/**
 * @see https://www.mail-archive.com/php-dev@lists.php.net/msg11576.html
 */
final class CallUserMethodRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    public function getRectorClass(): string
    {
        return CallUserMethodRector::class;
    }

    /**
     * @return string[]
     */
    public function provideDataForTest(): iterable
    {
        yield [__DIR__ . '/Fixture/fixture.php.inc'];
    }
}
