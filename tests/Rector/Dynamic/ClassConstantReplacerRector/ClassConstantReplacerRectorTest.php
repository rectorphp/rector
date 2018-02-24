<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Dynamic\ClassConstantReplacerRector;

use Rector\Testing\PHPUnit\AbstractConfigurableRectorTestCase;

final class ClassConstantReplacerRectorTest extends AbstractConfigurableRectorTestCase
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

    protected function provideConfig(): string
    {
        return __DIR__ . '/config/rector.yml';
    }
}
