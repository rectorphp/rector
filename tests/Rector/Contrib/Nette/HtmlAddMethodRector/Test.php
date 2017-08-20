<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Contrib\Nette\HtmlAddMethodRector;

use Rector\Rector\Contrib\Nette\HtmlAddMethodRectorRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class Test extends AbstractRectorTestCase
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
            __DIR__ . '/Wrong/SomeClass.php',
            __DIR__ . '/Correct/SomeClass.php'
        );
    }

    /**
     * @return string[]
     */
    protected function getRectorClasses(): array
    {
        return [HtmlAddMethodRectorRector::class];
    }
}
