<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Contrib\Nette\Utils\NetteObjectToSmartTraitRector;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;

/**
 * @covers \Rector\Rector\Contrib\Nette\Utils\NetteObjectToSmartTraitRector
 */
final class NetteObjectToSmartTraitRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideWrongToFixedFiles()
     */
    public function test(string $wrong, string $fixed): void
    {
        if (PHP_VERSION > '7.2.0') {
            $this->markTestSkipped('This test needs PHP 7.1 or lower.');
        }

        $this->doTestFileMatchesExpectedContent($wrong, $fixed);
    }

    /**
     * @return string[][]
     */
    public function provideWrongToFixedFiles(): array
    {
        return [
            [__DIR__ . '/Wrong/wrong.php.inc', __DIR__ . '/Correct/correct.php.inc'],
            [__DIR__ . '/Wrong/wrong2.php.inc', __DIR__ . '/Correct/correct2.php.inc'],
            [__DIR__ . '/Wrong/wrong3.php.inc', __DIR__ . '/Correct/correct3.php.inc'],
            [__DIR__ . '/Wrong/wrong4.php.inc', __DIR__ . '/Correct/correct4.php.inc'],
        ];
    }

    protected function provideConfig(): string
    {
        return __DIR__ . '/config.yml';
    }
}
