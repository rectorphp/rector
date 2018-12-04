<?php declare(strict_types=1);

namespace Rector\Tests\Rector\RectorOrder;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;

/**
 * Covers https://github.com/rectorphp/rector/pull/266#issuecomment-355725764
 */
final class RectorOrderTest extends AbstractRectorTestCase
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
