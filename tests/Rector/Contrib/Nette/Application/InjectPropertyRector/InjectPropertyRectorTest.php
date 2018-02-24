<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Contrib\Nette\Application\InjectPropertyRector;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;

/**
 * @covers \Rector\Rector\Contrib\Nette\Application\InjectPropertyRector
 */
final class InjectPropertyRectorTest extends AbstractRectorTestCase
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
        return __DIR__ . '/config.yml';
    }}