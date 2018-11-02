<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\Assign\AssignArrayToStringRector;

use Iterator;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

/**
 * @covers \Rector\Php\Rector\Assign\AssignArrayToStringRector
 */
final class AssignArrayToStringRectorTest extends AbstractRectorTestCase
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
        // https://www.drupal.org/files/issues/adaptivetheme-php_string_cast_array-2832900-1.patch
        yield [__DIR__ . '/Wrong/wrong.php.inc', __DIR__ . '/Correct/correct.php.inc'];
        // https://www.saotn.org/fatal-error-operator-not-supported-strings-php-71/
        yield [__DIR__ . '/Wrong/wrong2.php.inc', __DIR__ . '/Correct/correct2.php.inc'];
        yield [__DIR__ . '/Wrong/wrong3.php.inc', __DIR__ . '/Correct/correct3.php.inc'];
        // https://github.com/benkeen/generatedata/issues/410
        yield [__DIR__ . '/Wrong/wrong4.php.inc', __DIR__ . '/Correct/correct4.php.inc'];
        // https://flexicontent.org/forum/30-feature-requests/55502-solved-an-error-has-occurred-0-operator-not-supported-for-strings.html
        yield [__DIR__ . '/Wrong/wrong5.php.inc', __DIR__ . '/Correct/correct5.php.inc'];
        yield [__DIR__ . '/Wrong/wrong6.php.inc', __DIR__ . '/Correct/correct6.php.inc'];
        yield [__DIR__ . '/Wrong/wrong7.php.inc', __DIR__ . '/Correct/correct7.php.inc'];
        yield [__DIR__ . '/Wrong/wrong8.php.inc', __DIR__ . '/Correct/correct8.php.inc'];
        yield [__DIR__ . '/Wrong/wrong9.php.inc', __DIR__ . '/Correct/correct9.php.inc'];
    }

    protected function provideConfig(): string
    {
        return __DIR__ . '/config.yml';
    }
}
