<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Contrib\Nette\Bootstrap\RemoveConfiguratorConstantsRector;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;

/**
 * @covers \Rector\Rector\Contrib\Nette\Bootstrap\RemoveConfiguratorConstantsRector
 */
final class RemoveConfiguratorConstantsRectorTest extends AbstractRectorTestCase
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