<?php declare(strict_types=1);

namespace Rector\Symfony\Tests\Rector\FrameworkBundle\GetToConstructorInjectionRector;

use Rector\Exception\Configuration\InvalidConfigurationException;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

/**
 * @covers \Rector\Symfony\Rector\FrameworkBundle\GetToConstructorInjectionRector
 */
final class InvalidConfigurationConstructorInjectionRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage(
            'Make sure "kernel_class" parameters is set in rector.yml in "parameters:" section'
        );

        $this->doTestFiles([__DIR__ . '/Wrong/wrong.php.inc']);
    }

    protected function provideConfig(): string
    {
        return __DIR__ . '/invalid-config.yml';
    }
}
