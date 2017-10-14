<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Contrib\Symfony\HttpKernel\GetterToPropertyRector;

use Rector\Rector\Contrib\Symfony\HttpKernel\GetterToPropertyRector;
use Rector\Testing\PHPUnit\AbstractConfigurableRectorTestCase;

final class Test extends AbstractConfigurableRectorTestCase
{
    public function test(): void
    {
        $this->doTestFileMatchesExpectedContent(
            __DIR__ . '/Wrong/wrong.php.inc',
            __DIR__ . '/Correct/correct.php.inc'
        );

        $this->doTestFileMatchesExpectedContent(
            __DIR__ . '/Wrong/wrong2.php.inc',
            __DIR__ . '/Correct/correct2.php.inc'
        );

        $this->doTestFileMatchesExpectedContent(
            __DIR__ . '/Wrong/wrong3.php.inc',
            __DIR__ . '/Correct/correct3.php.inc'
        );

        $this->doTestFileMatchesExpectedContent(
            __DIR__ . '/Wrong/wrong4.php.inc',
            __DIR__ . '/Correct/correct4.php.inc'
        );
    }

    /**
     * @return string[]
     */
    protected function getRectorClasses(): array
    {
        return [GetterToPropertyRector::class];
    }

    protected function provideConfig(): string
    {
        return __DIR__ . '/Source/rector.yml';
    }
}
