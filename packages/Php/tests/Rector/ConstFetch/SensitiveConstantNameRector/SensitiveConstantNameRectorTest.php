<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\ConstFetch\SensitiveConstantNameRector;

use Iterator;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

/**
 * @covers \Rector\Php\Rector\ConstFetch\SensitiveConstantNameRector
 */
final class SensitiveConstantNameRectorTest extends AbstractRectorTestCase
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
    }

    protected function provideConfig(): string
    {
        return __DIR__ . '/config.yml';
    }
}
