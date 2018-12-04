<?php declare(strict_types=1);

namespace Rector\Tests\Rector\MethodCall\StaticMethodNameReplacerRector;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;

/**
 * @covers \Rector\Rector\MethodCall\StaticMethodNameReplacerRector
 */
final class StaticMethodNameReplacerRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Wrong/wrong.php.inc', __DIR__ . '/Wrong/wrong2.php.inc']);
    }

    protected function provideConfig(): string
    {
        return __DIR__ . '/config.yml';
    }
}
