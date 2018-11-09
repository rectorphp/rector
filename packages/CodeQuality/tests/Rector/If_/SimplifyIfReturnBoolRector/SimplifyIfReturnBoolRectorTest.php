<?php declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\If_\SimplifyIfReturnBoolRector;

use Iterator;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

/**
 * @covers \Rector\CodeQuality\Rector\If_\SimplifyIfReturnBoolRector
 */
final class SimplifyIfReturnBoolRectorTest extends AbstractRectorTestCase
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
//        yield [__DIR__ . '/Wrong/wrong.php.inc', __DIR__ . '/Correct/correct.php.inc'];
//        yield [__DIR__ . '/Wrong/wrong2.php.inc', __DIR__ . '/Correct/correct2.php.inc'];
//        yield [__DIR__ . '/Wrong/wrong3.php.inc', __DIR__ . '/Correct/correct3.php.inc'];
//        yield [__DIR__ . '/Wrong/wrong4.php.inc', __DIR__ . '/Correct/correct4.php.inc'];
//        yield [__DIR__ . '/Wrong/wrong5.php.inc', __DIR__ . '/Correct/correct5.php.inc'];
//        yield [__DIR__ . '/Wrong/wrong6.php.inc', __DIR__ . '/Correct/correct6.php.inc'];
//        yield [__DIR__ . '/Wrong/wrong7.php.inc', __DIR__ . '/Correct/correct7.php.inc'];
//        yield [__DIR__ . '/Wrong/wrong8.php.inc', __DIR__ . '/Correct/correct8.php.inc'];
//        yield [__DIR__ . '/Wrong/wrong9.php.inc', __DIR__ . '/Correct/correct9.php.inc'];
        yield [__DIR__ . '/Wrong/wrong10.php.inc', __DIR__ . '/Correct/correct10.php.inc'];
    }

    protected function provideConfig(): string
    {
        return __DIR__ . '/config.yml';
    }
}
