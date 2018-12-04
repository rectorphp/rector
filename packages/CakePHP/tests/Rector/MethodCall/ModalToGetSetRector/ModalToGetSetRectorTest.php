<?php declare(strict_types=1);

namespace Rector\CakePHP\Tests\Rector\MethodCall\ModalToGetSetRector;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;

/**
 * @covers \Rector\CakePHP\Rector\MethodCall\ModalToGetSetRector
 */
final class ModalToGetSetRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Wrong/wrong.php.inc',
            __DIR__ . '/Wrong/wrong2.php.inc',
            __DIR__ . '/Wrong/wrong3.php.inc',
        ]);
    }

    protected function provideConfig(): string
    {
        return __DIR__ . '/config.yml';
    }
}
