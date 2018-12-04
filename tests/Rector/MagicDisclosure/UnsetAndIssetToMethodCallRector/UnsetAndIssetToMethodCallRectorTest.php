<?php declare(strict_types=1);

namespace Rector\Tests\Rector\MagicDisclosure\UnsetAndIssetToMethodCallRector;

use Iterator;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

/**
 * @covers \Rector\Rector\MagicDisclosure\UnsetAndIssetToMethodCallRector
 */
final class UnsetAndIssetToMethodCallRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideFiles()
     */
    public function test(string $wrong, string $fixed): void
    {
        $this->doTestFileMatchesExpectedContent($wrong, $fixed);
    }

    public function provideFiles(): Iterator
    {
        yield [__DIR__ . '/Wrong/wrong.php.inc', __DIR__ . '/Correct/correct.php.inc'];
    }

    protected function provideConfig(): string
    {
        return __DIR__ . '/config.yml';
    }
}
