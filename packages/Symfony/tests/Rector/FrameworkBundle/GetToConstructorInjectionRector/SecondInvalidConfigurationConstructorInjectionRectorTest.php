<?php declare(strict_types=1);

namespace Rector\Symfony\Tests\FrameworkBundle\GetToConstructorInjectionRector;

use Rector\Exception\Configuration\InvalidConfigurationException;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use function Safe\sprintf;

/**
 * @covers \Rector\Symfony\Rector\FrameworkBundle\GetToConstructorInjectionRector
 */
final class SecondInvalidConfigurationConstructorInjectionRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage(sprintf(
            'Kernel class "%s" provided in "parameters > %s" is not autoloadable. ' .
            'Make sure composer.json of your application is valid and rector is loading "vendor/autoload.php" of your application.',
            'NonExistingClass',
            'kernel_class'
        ));

        $this->doTestFileMatchesExpectedContent(__DIR__ . '/Wrong/wrong.php.inc', __DIR__ . '/Correct/correct.php.inc');
    }

    protected function provideConfig(): string
    {
        return __DIR__ . '/invalid-config-2.yml';
    }
}
