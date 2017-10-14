<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Dynamic\MethodArgumentChangerRector;

use Rector\Rector\Dynamic\MethodArgumentChangerRector;
use Rector\Testing\PHPUnit\AbstractConfigurableRectorTestCase;

final class Test extends AbstractConfigurableRectorTestCase
{
    protected function provideConfig(): string
    {
        return __DIR__ . '/config/rector.yml';
    }

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
    }

    /**
     * @return string[]
     */
    protected function getRectorClasses(): array
    {
        return [MethodArgumentChangerRector::class];
    }
}
