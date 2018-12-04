<?php declare(strict_types=1);

namespace Rector\Tests\Rector\MethodCall\MethodNameReplacerRector;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;

/**
 * @covers \Rector\Rector\MethodCall\MethodNameReplacerRector
 */
final class MethodNameReplacerRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles(
            [[__DIR__ . '/Wrong/wrong.php.inc', __DIR__ . '/Correct/correct.php.inc'], [
                __DIR__ . '/Wrong/wrong2.php.inc',
                __DIR__ . '/Correct/correct2.php.inc',
            ], [__DIR__ . '/Wrong/wrong3.php.inc', __DIR__ . '/Correct/correct3.php.inc'], [
                __DIR__ . '/Wrong/wrong4.php.inc',
                __DIR__ . '/Correct/correct4.php.inc',
            ], [__DIR__ . '/Wrong/wrong5.php.inc', __DIR__ . '/Correct/correct5.php.inc'], [
                __DIR__ . '/Wrong/wrong6.php.inc',
                __DIR__ . '/Correct/correct6.php.inc',
            ], [__DIR__ . '/Wrong/SomeClass.php', __DIR__ . '/Correct/SomeClass.php']]
        );
    }

    protected function provideConfig(): string
    {
        return __DIR__ . '/config.yml';
    }
}
