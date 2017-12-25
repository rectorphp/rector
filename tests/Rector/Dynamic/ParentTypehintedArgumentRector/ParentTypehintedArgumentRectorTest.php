<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Dynamic\ParentTypehintedArgumentRector;

use Rector\Rector\Dynamic\ParentTypehintedArgumentRector;
use Rector\Testing\PHPUnit\AbstractConfigurableRectorTestCase;

final class ParentTypehintedArgumentRectorTest extends AbstractConfigurableRectorTestCase
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
        return [ParentTypehintedArgumentRector::class];
    }
}
