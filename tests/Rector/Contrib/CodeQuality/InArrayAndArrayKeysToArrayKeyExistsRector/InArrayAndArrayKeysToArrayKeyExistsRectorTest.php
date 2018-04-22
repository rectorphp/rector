<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Contrib\CodeQuality\InArrayAndArrayKeysToArrayKeyExistsRector;

use Iterator;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

/**
 * @covers \Rector\Rector\Contrib\CodeQuality\InArrayAndArrayKeysToArrayKeyExistsRector
 */
final class InArrayAndArrayKeysToArrayKeyExistsRectorTest extends AbstractRectorTestCase
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
