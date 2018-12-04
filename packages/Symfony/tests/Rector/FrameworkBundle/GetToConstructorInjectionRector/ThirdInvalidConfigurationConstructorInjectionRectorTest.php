<?php declare(strict_types=1);

namespace Rector\Symfony\Tests\Rector\FrameworkBundle\GetToConstructorInjectionRector;

use Rector\Exception\Configuration\InvalidConfigurationException;
use Rector\Symfony\Tests\FrameworkBundle\AbstractToConstructorInjectionRectorSource\SomeNonKernelClass;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symfony\Component\HttpKernel\Kernel;
use function Safe\sprintf;

/**
 * @covers \Rector\Symfony\Rector\FrameworkBundle\GetToConstructorInjectionRector
 */
final class ThirdInvalidConfigurationConstructorInjectionRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Kernel class "%s" provided in "parameters > %s" is not instance of "%s". Make sure it is.',
                SomeNonKernelClass::class,
                'kernel_class',
                Kernel::class
            )
        );

        $this->doTestFiles([__DIR__ . '/Wrong/wrong.php.inc']);
    }

    protected function provideConfig(): string
    {
        return __DIR__ . '/invalid-config-3.yml';
    }
}
