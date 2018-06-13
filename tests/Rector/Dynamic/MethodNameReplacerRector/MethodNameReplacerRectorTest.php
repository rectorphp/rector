<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Dynamic\MethodNameReplacerRector;

use Iterator;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

/**
 * @covers \Rector\Rector\Dynamic\MethodNameReplacerRector
 */
final class MethodNameReplacerRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/wrong/wrong.php.inc', __DIR__ . '/correct/correct.php.inc'];
        yield [__DIR__ . '/wrong/wrong2.php.inc', __DIR__ . '/correct/correct2.php.inc'];
        yield [__DIR__ . '/wrong/wrong3.php.inc', __DIR__ . '/correct/correct3.php.inc'];
        yield [__DIR__ . '/wrong/wrong4.php.inc', __DIR__ . '/correct/correct4.php.inc'];
        yield [__DIR__ . '/wrong/wrong5.php.inc', __DIR__ . '/correct/correct5.php.inc'];
        yield [__DIR__ . '/wrong/wrong6.php.inc', __DIR__ . '/correct/correct6.php.inc'];
        yield [__DIR__ . '/wrong/wrong7.php.inc', __DIR__ . '/correct/correct7.php.inc'];
        yield [__DIR__ . '/wrong/SomeClass.php', __DIR__ . '/correct/SomeClass.php'];
    }

    protected function provideConfig(): string
    {
        return __DIR__ . '/config.yml';
    }
}
