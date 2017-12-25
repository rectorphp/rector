<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Dynamic\NamespaceReplacerRector;

use Rector\Rector\Dynamic\NamespaceReplacerRector;
use Rector\Testing\PHPUnit\AbstractConfigurableRectorTestCase;

final class NamespaceReplacerRectorTest extends AbstractConfigurableRectorTestCase
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

        $this->doTestFileMatchesExpectedContent(
            __DIR__ . '/Wrong/wrong5.php.inc',
            __DIR__ . '/Correct/correct5.php.inc'
        );
    }

    protected function provideConfig(): string
    {
        return __DIR__ . '/config/rector.yml';
    }

    /**
     * @return string[]
     */
    protected function getRectorClasses(): array
    {
        return [NamespaceReplacerRector::class];
    }
}
