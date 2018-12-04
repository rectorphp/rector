<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Constant\ClassConstantReplacerRector;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;

/**
 * @see \Rector\Rector\Constant\ClassConstantReplacerRector
 */
final class ClassConstantReplacerRectorTest extends AbstractRectorTestCase
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
