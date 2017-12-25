<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Contrib\Symfony\Console\CommandToConstructorInjectionRector;

use Rector\Rector\Contrib\Symfony\Console\CommandToConstructorInjectionRector;
use Rector\Testing\PHPUnit\AbstractConfigurableRectorTestCase;

final class CommandToConstructorInjectionRectorTest extends AbstractConfigurableRectorTestCase
{
    /**
     * @dataProvider provideWrongToFixedFiles()
     */
    public function test(string $wrong, string $fixed): void
    {
        $this->doTestFileMatchesExpectedContent($wrong, $fixed);
    }

    /**
     * @return string[][]
     */
    public function provideWrongToFixedFiles(): array
    {
        return [
            [__DIR__ . '/Wrong/wrong.php.inc', __DIR__ . '/Correct/correct.php.inc'],
        ];
    }

    /**
     * @return string[]
     */
    protected function getRectorClasses(): array
    {
        return [CommandToConstructorInjectionRector::class];
    }

    protected function provideConfig(): string
    {
        return __DIR__ . '/Source/rector.yml';
    }
}
