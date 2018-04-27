<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Contrib\Symfony\FrameworkBundle\GetToConstructorInjectionRector;

use Rector\Exception\Configuration\InvalidConfigurationException;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Tests\Rector\Contrib\Symfony\FrameworkBundle\GetToConstructorInjectionRector\Source\SomeNonKernelClass;
use Symfony\Component\HttpKernel\Kernel;

/**
 * @covers \Rector\Rector\Contrib\Symfony\FrameworkBundle\GetToConstructorInjectionRector
 */
final class ThirdInvalidConfigurationConstructorInjectionRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage(sprintf(
            'Kernel class "%s" provided in "parameters > %s" is not instance of "%s". Make sure it is.',
            SomeNonKernelClass::class,
            'kernel_class',
            Kernel::class
        ));

        $this->doTestFileMatchesExpectedContent(__DIR__ . '/Wrong/wrong.php.inc', __DIR__ . '/Correct/correct.php.inc');
    }

    protected function provideConfig(): string
    {
        return __DIR__ . '/invalid-config-3.yml';
    }
}
