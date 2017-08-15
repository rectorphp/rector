<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Contrib\Symfony\GetterToPropertyRector;

use Rector\Rector\Contrib\Symfony\GetterToPropertyRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class Test extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFileMatchesExpectedContent(
            __DIR__ . '/wrong/wrong.php.inc',
            __DIR__ . '/correct/correct.php.inc'
        );

        $this->doTestFileMatchesExpectedContent(
            __DIR__ . '/wrong/wrong2.php.inc',
            __DIR__ . '/correct/correct2.php.inc'
        );

        $this->doTestFileMatchesExpectedContent(
            __DIR__ . '/wrong/wrong3.php.inc',
            __DIR__ . '/correct/correct3.php.inc'
        );
    }

    /**
     * @return string[]
     */
    protected function getRectorClasses(): array
    {
        return [GetterToPropertyRector::class];
    }
}
