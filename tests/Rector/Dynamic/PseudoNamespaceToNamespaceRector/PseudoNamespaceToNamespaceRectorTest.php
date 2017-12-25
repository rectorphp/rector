<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Dynamic\PseudoNamespaceToNamespaceRector;

use Rector\Rector\Dynamic\PseudoNamespaceToNamespaceRector;
use Rector\Testing\PHPUnit\AbstractConfigurableRectorTestCase;

final class PseudoNamespaceToNamespaceRectorTest extends AbstractConfigurableRectorTestCase
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

    protected function provideConfig(): string
    {
        return __DIR__ . '/config/rector.yml';
    }

    /**
     * @return string[]
     */
    protected function getRectorClasses(): array
    {
        return [PseudoNamespaceToNamespaceRector::class];
    }
}
