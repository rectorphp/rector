<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Function_\FunctionToMethodCallRector;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;

/**
 * @see \Rector\Rector\Function_\FunctionToMethodCallRector
 */
final class FunctionToMethodCallRectorTest extends AbstractRectorTestCase
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
