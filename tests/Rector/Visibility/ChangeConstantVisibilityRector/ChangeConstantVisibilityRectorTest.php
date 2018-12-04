<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Visibility\ChangeConstantVisibilityRector;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;

/**
 * @covers \Rector\Rector\Visibility\ChangeConstantVisibilityRector
 */
final class ChangeConstantVisibilityRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Wrong/wrong2.php.inc']);
    }

    protected function provideConfig(): string
    {
        return __DIR__ . '/config.yml';
    }
}
