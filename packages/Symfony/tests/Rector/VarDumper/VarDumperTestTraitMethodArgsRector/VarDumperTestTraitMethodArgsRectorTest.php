<?php declare(strict_types=1);

namespace Rector\Symfony\Tests\Rector\VarDumper\VarDumperTestTraitMethodArgsRector;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;

/**
 * @covers \Rector\Symfony\Rector\VarDumper\VarDumperTestTraitMethodArgsRector
 */
final class VarDumperTestTraitMethodArgsRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([[__DIR__ . '/Wrong/wrong.php.inc', __DIR__ . '/Correct/correct.php.inc']]);
    }

    protected function provideConfig(): string
    {
        return __DIR__ . '/config.yml';
    }
}
