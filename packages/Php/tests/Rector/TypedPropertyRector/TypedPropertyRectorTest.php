<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\TypedPropertyRector;

use Iterator;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

/**
 * @covers \Rector\Php\Rector\TypedPropertyRector
 */
final class TypedPropertyRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Wrong/ClassWithProperty.php', __DIR__ . '/Correct/correct.php.inc'];
        yield [__DIR__ . '/Wrong/ClassWithClassProperty.php', __DIR__ . '/Correct/correct2.php.inc'];
        yield [__DIR__ . '/Wrong/ClassWithNullableProperty.php', __DIR__ . '/Correct/correct3.php.inc'];
        yield [__DIR__ . '/Wrong/ClassWithStaticProperty.php', __DIR__ . '/Correct/correct4.php.inc'];
        yield [__DIR__ . '/Wrong/DefaultValues.php', __DIR__ . '/Correct/correct5.php.inc'];
        // based on: https://wiki.php.net/rfc/typed_properties_v2#supported_types
        yield [__DIR__ . '/Wrong/MatchTypes.php', __DIR__ . '/Correct/correct6.php.inc'];
    }

    protected function provideConfig(): string
    {
        return __DIR__ . '/config.yml';
    }
}
