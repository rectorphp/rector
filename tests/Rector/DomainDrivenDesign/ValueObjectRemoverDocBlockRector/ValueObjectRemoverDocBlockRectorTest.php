<?php declare(strict_types=1);

namespace Rector\Tests\Rector\DomainDrivenDesign\ValueObjectRemoverDocBlockRector;

use Iterator;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

/**
 * @covers \Rector\Rector\DomainDrivenDesign\ValueObjectRemover\ValueObjectRemoverDocBlockRector
 */
final class ValueObjectRemoverDocBlockRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Wrong/wrong.php', __DIR__ . '/Correct/correct.php'];
        yield [__DIR__ . '/Wrong/wrong2.php', __DIR__ . '/Correct/correct2.php'];
        yield [__DIR__ . '/Wrong/wrong3.php', __DIR__ . '/Correct/correct3.php'];
    }

    protected function provideConfig(): string
    {
        return __DIR__ . '/config.yml';
    }
}
