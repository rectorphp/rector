<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Namespace_\PseudoNamespaceToNamespaceRector;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;

/**
 * @covers \Rector\Rector\Namespace_\PseudoNamespaceToNamespaceRector
 */
final class PseudoNamespaceToNamespaceRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Wrong/wrong.php.inc',
            __DIR__ . '/Wrong/wrong2.php.inc',
            __DIR__ . '/Wrong/wrong3.php.inc',
            __DIR__ . '/Wrong/wrong4.php.inc',
            __DIR__ . '/Wrong/wrong5.php.inc',
        ]);
    }

    protected function provideConfig(): string
    {
        return __DIR__ . '/config.yml';
    }
}
