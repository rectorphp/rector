<?php declare(strict_types=1);

namespace Rector\Tests\Issues\Issue594;

use Iterator;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class Issue594Test extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Wrong/wrong594.php', __DIR__ . '/Correct/correct594.php'];
    }

    protected function provideConfig(): string
    {
        return __DIR__ . '/config594.yml';
    }
}
