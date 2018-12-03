<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\FunctionLike\ExceptionHandlerTypehintRector;

use Iterator;
use Rector\Php\Rector\FunctionLike\ExceptionHandlerTypehintRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ExceptionHandlerTypehintRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideNullableFiles()
     */
    public function testNullableType(string $wrong, string $fixed): void
    {
        $this->doTestFileMatchesExpectedContent($wrong, $fixed);
    }

    public function test(): void
    {
        $this->doTestFiles([[__DIR__ . '/Wrong/wrong.php.inc', __DIR__ . '/Correct/correct.php.inc']]);
    }

    public function provideNullableFiles(): Iterator
    {
        yield [__DIR__ . '/Wrong/wrong_nullable.php.inc', __DIR__ . '/Correct/correct_nullable.php.inc'];
    }

    public function getRectorClass(): string
    {
        return ExceptionHandlerTypehintRector::class;
    }
}
