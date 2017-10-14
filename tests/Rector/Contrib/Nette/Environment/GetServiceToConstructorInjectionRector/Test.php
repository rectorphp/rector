<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Contrib\Nette\Environment\GetServiceToConstructorInjectionRector;

use Rector\Rector\Contrib\Nette\Environment\GetServiceToConstructorInjectionRector;
use Rector\Testing\PHPUnit\AbstractConfigurableRectorTestCase;

final class Test extends AbstractConfigurableRectorTestCase
{
    public function test(): void
    {
        $this->doTestFileMatchesExpectedContent(
            __DIR__ . '/Wrong/wrong.php.inc',
            __DIR__ . '/Correct/correct.php.inc'
        );
    }

    /**
     * @return string[]
     */
    protected function getRectorClasses(): array
    {
        return [GetServiceToConstructorInjectionRector::class];
    }

    protected function provideConfig(): string
    {
        return __DIR__ . '/Source/rector.yml';
    }
}
