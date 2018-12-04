<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Typehint\ReturnTypehintRector;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;

/**
 * @see \Rector\Rector\Typehint\ReturnTypehintRector
 */
final class ReturnTypehintRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Wrong/wrong.php.inc']);
    }

    protected function provideConfig(): string
    {
        return __DIR__ . '/config.yml';
    }
}
